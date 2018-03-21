import Pyro4
from pprint import pprint

"""
New Script: Simulates the primary replica "crashing" by changing its uri
from "gamestore.primary" to "gamestore.temp" so it is no longer accessible by
the frontend
"""

def main():
    ns = Pyro4.locateNS()
    primary_uri = ns.lookup("gamestore.primary")
    ns.register("gamestore.temp", primary_uri)
    if ns.remove("gamestore.primary"):
        print("successfully disabled primary replica",
              "(Moved it from 'gamestore.primary' to 'gamestore.temp')",
              sep="\n")
        print("List of currently active servers:")
        pprint(ns.list(), indent=2, width=1)


if __name__ == "__main__":
    main()
