THIS assignment was written in python3, as such it may not work on python2.

I made the system to work on my local machine using localhost so I cannot
guarantee it will work if you connect the components over the internet (i.e.
using an IP address other than 127.0.0.1)

To run the system, run the following scripts (in different shell windows) in
the following order.

| No. | Instruction          | Terminal command                        |
------------------------------------------------------------------------
| 1   | Start the nameserver | pyro4-ns (or "python3 -m Pyro4.naming") |
| 2   | Start the replicas   | python3 server_replica.py               |
| 3   | Start the frontend   | python3 server_frontend.py              |
| 4   | Start the client     | python3 client.py                       |


To simulate the primary replica crashing, run "python3 disable_primary.py" in a
new window. If you switch back to the client and make a request, the frontend
will automatically promote one of the backups to become the new primary.

To readmit the crashed primary replica as a backup, run
"python3 readmit_primary.py" in new window.
