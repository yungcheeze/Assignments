import json
import Pyro4
from pprint import pprint

"""
REPLICA SERVER PROGRAM
=======================

Code from old version (i.e. question 2) is marked with
"# --" at beginning of line.

Code from new version (i.e. question 3) is encapuslated with
"# ++" and "/++" comments
"""


@Pyro4.expose
@Pyro4.behavior(instance_mode="single")
class RequestLog(object):
    """
    Class that stores and maintains all requests (along with their unique
    identifier) issued by the frontend
    """

    def __init__(self):
        self.max_id = 0 # stores largest request identifier in the request log

        # request lod dictionary: key=request_id, value=request, response tuple
        self.request_log = {}

    def add_request(self, request_id, request, response):
        self.request_log[request_id] = {
            "request": request,
            "response": response
        }
        self.max_id = max(self.max_id, request_id)

    @property
    def id_max(self):
        return self.max_id

    def request_exists(self, request_id):
        try:
            self.request_log[request_id]
            return True
        except KeyError:
            return False

    def get_log_entry(self, request_id):
        try:
            entry = self.request_log[request_id]
            return entry
        except KeyError:
            return None


# ++
# New Code: GameStore class that can act as both a primary and a replica
@Pyro4.expose
class GameStore(object):
    """
    Hybrid Replica class capable of acting as a primary and backup
    """

    def __init__(self):
        # order list dictionary: key=username, value=array of ordered items
        self.order_list = {}
        # dictonary containind functions to deal with user requests
        self.handlers = {"add": self.add_items,
                         "get": self.get_order_history,
                         "delete": self.delete_item}

    def process_request_primary(self, request_string, request_id):
        """
        Request handler used by primary replica
        """
        request_log = Pyro4.Proxy("PYRONAME:gamestore.requestlog")

        # resend response if request_id already in log
        if request_log.request_exists(request_id):
            entry = request_log.get_log_entry(request_id)
            response_string = json.dumps(entry["response"])
            return response_string, request_id

        request = json.loads(request_string)
        username = request["username"]

        # Add user to order list if they don't exist
        if username not in self.order_list:
            self.order_list[username] = []

        # Handle user request
        handler = request["type"]
        response = self.handlers[handler](request)
        print("\nincoming request: ", request_string,
              "\nprimary post-request state:", self.order_list)
        request_log.add_request(request_id, request, response)
        request_log._pyroRelease()
        if request["type"] != "get":
            success, responses = self.update_backups(request_string, request_id)
        response_string = json.dumps(response)
        return response_string, request_id

    def process_request_backup(self, request_string, request_id):
        """
        Reuest handler used by backup replica
        """
        request = json.loads(request_string)
        username = request["username"]
        if username not in self.order_list:
            self.order_list[username] = []
        handler = request["type"]
        response = self.handlers[handler](request)
        print("backup post-request state:", self.order_list)
        response_string = json.dumps(response)
        return response_string

    def add_items(self, request):
        items = request["items"]
        response = {"success": True}
        if len(items) > 3:
            response["success"] = False
            response["message"] = "too many items\
                (limit = 3; {} provided)".format(len(items))
        else:
            username = request["username"]
            self.order_list[username].extend(items)
            response["message"] = "item(s) successfully added"
        return response

    def get_order_history(self, request):
        response = {"success": True}
        username = request["username"]
        response["items"] = self.order_list[username]
        return response

    def delete_item(self, request):
        item_no = request["item_no"]
        response = {"success": True}
        try:
            username = request["username"]
            item = self.order_list[username].pop(item_no)
            response["message"] = "item '{}' was successfully deleted ".format(item)
        except IndexError:
            response["success"] = False
            response["message"] = "invalid item number provided"
        return response

    def update_backups(self, request_string, request_id):
        """
        Function used by primary to update backups
        """
        backups = False
        responses = []
        with Pyro4.locateNS() as ns:
            for backup, backup_uri in ns.list(prefix="gamestore.backup").items():
                print("found backup", backup)
                proxy_object = Pyro4.Proxy(backup_uri)
                response = proxy_object.process_request_backup(request_string,
                                                               request_id)
                print("response:", response)
                responses.append(response)
                backups = True
        print("Success:", backups)
        return backups, responses

    # New functions: used to get and set the order list attribute
    @property
    def ord_list(self):
        return self.order_list

    @ord_list.setter
    def ord_list(self, ord_list):
        self.order_list = dict(ord_list)
# /++

@Pyro4.expose
class GameStorePrimary(object):
    """
    Primary Replica Class
    """

    def __init__(self):
        self.order_list = {}
        self.handlers = {"add": self.add_items,
                         "get": self.get_order_history,
                         "delete": self.delete_item}

    def process_request(self, request_string, request_id):
        """
        Main request handler
        """
        request_log = Pyro4.Proxy("PYRONAME:gamestore.requestlog")
        if request_log.request_exists(request_id):
            entry = request_log.get_log_entry(request_id)
            response_string = json.dumps(entry["response"])
            return response_string, request_id
        request = json.loads(request_string)
        username = request["username"]
        if username not in self.order_list:
            self.order_list[username] = []
        handler = request["type"]
        response = self.handlers[handler](request)
        request_log.add_request(request_id, request, response)
        request_log._pyroRelease()
        if request["type"] != "get":
            success, responses = self.update_backups(request_string, request_id)
        response_string = json.dumps(response)
        return response_string, request_id

    def add_items(self, request):
        items = request["items"]
        response = {"success": True}
        if len(items) > 3:
            response["success"] = False
            response["message"] = "too many items\
                (limit = 3; {} provided)".format(len(items))
        else:
            username = request["username"]
            self.order_list[username].extend(items)
            response["message"] = "items successfully added"
        return response

    def get_order_history(self, request):
        response = {"success": True}
        username = request["username"]
        response["items"] = self.order_list[username]
        return response

    def delete_item(self, request):
        item_no = request["item_no"]
        response = {"success": True}
        try:
            username = request["username"]
            item = self.order_list[username].pop(item_no)
            response["message"] = "item deleted: {}".format(item)
        except IndexError:
            response["success"] = False
            response["message"] = "invalid item number provided"
        return response

    def update_backups(self, request_string, request_id):
        backups = False
        responses = []
        with Pyro4.locateNS() as ns:
            for backup, backup_uri in ns.list(prefix="gamestore.backup").items():
                print("found backup", backup)
                proxy_object = Pyro4.Proxy(backup_uri)
                response = proxy_object.process_request(request_string,
                                                        request_id)
                print("response:", response)
                responses.append(response)
                backups = True
        print("Success:", backups)
        return backups, responses


@Pyro4.expose
class GameStoreBackup(object):
    """
    Backup Replica class
    """

    def __init__(self):
        self.order_list = {}
        self.handlers = {"add": self.add_items,
                         "get": self.get_order_history,
                         "delete": self.delete_item}

    def process_request(self, request_string, request_id):
        """
        Main request handler
        """
        request = json.loads(request_string)
        username = request["username"]
        if username not in self.order_list:
            self.order_list[username] = []
        handler = request["type"]
        response = self.handlers[handler](request)
        print("backup post-request state:", self.order_list)
        response_string = json.dumps(response)
        return response_string

    def add_items(self, request):
        items = request["items"]
        response = {"success": True}
        if len(items) > 3:
            response["success"] = False
            response["message"] = "too many items\
                (limit = 3; {} provided)".format(len(items))
        else:
            username = request["username"]
            self.order_list[username].extend(items)
            response["message"] = "items successfully added"
        return response

    def get_order_history(self, request):
        response = {"success": True}
        username = request["username"]
        response["items"] = self.order_list[username]
        return response

    def delete_item(self, request):
        item_no = request["item_no"]
        response = {"success": True}
        try:
            username = request["username"]
            item = self.order_list[username].pop(item_no)
            response["message"] = "item deleted: {}".format(item)
        except IndexError:
            response["success"] = False
            response["message"] = "invalid item number provided"
        return response


def main():
    """
    Main script function: Initialises replicas and starts the main request loop
    """
    # -- primary = GameStorePrimary()
    # -- backup1 = GameStoreBackup()
    # -- backup2 = GameStoreBackup()
    # -- backup3 = GameStoreBackup()
    # ++
    # New Code: Initialise replicas with new GameStore class
    primary = GameStore()
    backup1 = GameStore()
    backup2 = GameStore()
    backup3 = GameStore()
    # /++
    with Pyro4.Daemon() as daemon:
        primary_uri = daemon.register(primary)
        backup1_uri = daemon.register(backup1)
        backup2_uri = daemon.register(backup2)
        backup3_uri = daemon.register(backup3)
        request_log_uri = daemon.register(RequestLog)
        with Pyro4.locateNS() as ns:
            ns.register("gamestore.primary", primary_uri)
            ns.register("gamestore.backup.1", backup1_uri)
            ns.register("gamestore.backup.2", backup2_uri)
            ns.register("gamestore.backup.3", backup3_uri)
            ns.register("gamestore.requestlog", request_log_uri)
        print("Replicas available.")
        pprint(Pyro4.locateNS().list(), width=1)
        daemon.requestLoop()

if __name__ == "__main__":
    main()
