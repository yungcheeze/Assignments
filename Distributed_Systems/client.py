import json
from exceptions import BadInputException
from socket import socket, AF_INET, SOCK_STREAM
import ui


def request_loop():
    """
    Main loop that runs when the script starts
    """
    ui.init()
    username = ""
    while username == "":
        try:
            username = ui.get_username()
        except BadInputException as e:
            print("\n" + str(e) + "\n")
            continue

    print("Hello", username)
    while 1:
        try:
            action = ui.single_action_query()
            request_string = ""
            response_string = ""
            if action == "quit":
                print("Bye Bye!")
                break
            elif action == "add":
                user_input = ui.single_add_query()
                request_string = place_order(user_input, username)
                response_string = get_response(request_string)
            elif action == "get":
                request_string = get_orders(username)
                response_string = get_response(request_string)
            else:
                # print cached version of orders
                response = json.loads(get_response(get_orders(username)))
                ui.print_response(response)
                if len(response["items"]) > 0:
                    user_input = ui.single_cancel_query()
                    request_string = cancel_order(user_input, username)
                    response_string = get_response(request_string)
        except BadInputException as e:
            print("\n" + str(e) + "\n")
            continue
        response = json.loads(response_string)
        ui.print_response(response)


def get_response(request_string):
    """
    Makes socket connection to the frontend and sends the request_string
    as a json-encoded byte strem
    """
    serverName = 'localhost'
    serverPort = 12000
    clientSocket = socket(AF_INET, SOCK_STREAM)
    clientSocket.connect((serverName, serverPort))
    byte_stream = request_string.encode()
    clientSocket.send(byte_stream)
    response = clientSocket.recv(1024)
    clientSocket.close()
    return str(response, "utf-8")


def place_order(item_string, username):
    """
    parameters:
        item_string: single string of alphanumeric words separated by commas
        username: alphanumeric string of username

    creates json string sent to frontend used to add new items.
    """
    items = list(x.strip() for x in item_string.split(","))
    request = {"type": "add", "items": items, "username": username}
    request_string = json.dumps(request)
    return request_string


def get_orders(username):
    """
    parameters:
        username: alphanumeric string of username

    creates json string sent to frontend used to get order history
    """
    request = {"type": "get", "username": username}
    request_string = json.dumps(request)
    return request_string


def cancel_order(item_number, username):
    """
    parameters:
        item_number: string representation of integer corresponding to order
                     item
        username: alphanumeric string of username

    creates json string sent to frontend used to get order history
    """
    item_number = item_number.strip()
    request = {"type": "delete",
               "item_no": int(item_number),
               "username": username}
    request_string = json.dumps(request)
    return request_string

if __name__ == "__main__":
    request_loop()
