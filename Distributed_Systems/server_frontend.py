import json
# from exceptions import BadInputException
from socket import socket, AF_INET, SOCK_STREAM
import threading
import Pyro4
import sys

"""
FRONTEND SERVER PROGRAM
=======================

Code from old version (i.e. question 2) is marked with
"# --" at beginning of line.

Code from new version (i.e. question 3) is encapuslated with
"# ++" and "/++" comments
"""


def main():
    """
    Main server request loop.
    Accepts incoming connection from client and forwards to primary replica on a
    new thread
    """
    serverPort = 12000
    serverSocket = socket(AF_INET, SOCK_STREAM)
    serverSocket.bind(('', serverPort))
    serverSocket.listen(1)
    print("Server active")
    request_id = get_max_id()
    while 1:
        connectionSocket, addr = serverSocket.accept()
        print(addr)
        request_id += 1
        t = threading.Thread(target=handle_connection,
                             args=(connectionSocket, request_id,))
        t.daemon = True
        t.start()


def handle_connection(connectionSocket, request_id):
    """
    function used by thread to connect to primary replica, and return the
    response to the client.
    """
    request_string = connectionSocket.recv(1024)
    r_string = str(request_string, 'utf-8')
    print("request:", r_string)
    try:
        response_string = process_request(request_id, r_string)
        print("response:", response_string, "id =", request_id)
        connectionSocket.send(response_string.encode())
    except Pyro4.errors.NamingError:
        print("couldn't find primary replica")
        # -- response = {"error": "couldn't connect to main server"}
        # -- response = json.dumps(response)
        # -- connectionSocket.send("couldn't find primary replica".encode())

        # ++
        # New code: try to promote primary if connection failed.
        #           (returns error to client if no backup found)
        try:
            promote_new_primary()
            response_string = process_request(request_id, r_string)
            print("response:", response_string, "id =", request_id)
            connectionSocket.send(response_string.encode())
        except:
            print(sys.exc_info[0])
            print("couldn't find new backup")
            response = {"error": "could not connect to main server"}
            response = json.dumps(response)
            connectionSocket.send(response.encode())
        # /++
    connectionSocket.close()


def process_request(request_id, request_string):
    """
    Function that actually creates the RMI Proxy and retrieves the response
    parameters:
        request_id: unique identifier of the request
        request_string: json-encoded string containing the request

    returns json-encoded string containing the response
    """
    uri = Pyro4.locateNS().lookup("gamestore.primary")
    gameStore = Pyro4.Proxy(uri)
    response_string, response_id = \
        gameStore.process_request_primary(request_string, request_id)
    return response_string


# ++
# New code: function used to promote new primary replica
def promote_new_primary():
    """
    Function called when primary replica not found.
    Promotes a backup to become the new primary by changind its uri
    from "gamestore.backup.*" to "gamestore.primary"
    """
    ns = Pyro4.locateNS()
    ns_alias, uri =\
        list(ns.list(prefix="gamestore.backup").items())[0]
    ns.register("gamestore.primary", uri)
    ns.remove(ns_alias)
    print("Promoted {} to 'gamestore.primary'".format(ns_alias))
# /++


def get_max_id():
    """
    Called when server starts up.
    Initialises the request_id counter by getting the highest request_id
    stored in the request log.
    This prevents the frontend from sending an already used request_id in the
    event thet it crashes
    """
    request_log = Pyro4.Proxy("PYRONAME:gamestore.requestlog")
    max_id = request_log.id_max
    request_log._pyroRelease()
    return max_id

if __name__ == "__main__":
    main()
