
import Pyro4
from pprint import pprint

"""
New Script: Readmits "crashed" primary replica into the distributed system.
i.e. copy's one one the backup's order_lists onto the primary (so it's in a
valid state) then changes it's uri from "gamestore.temp" to "gamestore.backup.*"o
so it can be accessed by the primary replica during update requests
"""

def main():
    ns = Pyro4.locateNS()
    primary_uri = ns.lookup("gamestore.temp")
    try:
        arbitrary_backup = list(ns.list(prefix="gamestore.backup").values())[0]
    except:
        arbitrary_backup = list(ns.list(prefix="gamestore").values())[0]
    arbitrary_backup = Pyro4.Proxy(arbitrary_backup)
    primary = Pyro4.Proxy(primary_uri)
    primary.ord_list = arbitrary_backup.ord_list
    max_n = 0
    for backup_name, backup_uri in ns.list(prefix="gamestore.backup").items():
        n = int(backup_name[17:])
        max_n = max(n, max_n)
    n = str(max_n + 1)
    ns_alias = "gamestore.backup." + n
    ns.register(ns_alias, primary_uri)
    ns.remove("gamestore.temp")
    print("sucessfully readmitted old primary as backup")
    print("(Moved it from 'gamestore.temp' to {})".format(ns_alias))
    print("List of currently active servers:")
    pprint(ns.list(), indent=2, width=1)


if __name__ == "__main__":
    main()
