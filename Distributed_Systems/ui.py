from exceptions import BadInputException
import re
"""
Set of functions responsible for getting input from the user and displaying
output to the terminal
"""


def init():
    """
    Welcome Message
    """
    print("Welcome to the game store")


def get_username():
    """
    Get username from username. ensures its is alphanumeric before passing it
    to the client
    """
    username = input("please enter a username: ")
    if not username.isalnum():
        raise BadInputException("username must be alphanumeric")
    return username


def single_action_query():
    """
    Ask user what action they wish to do.
    passes valid action string to client.
    """
    action_dict = {"1": ("add"), "2": "get", "3": "delete", "4": "quit"}
    action = input("What would you like to do?\
                   (Enter a number corresponding to the action)\n\
                   1. Make an order\n\
                   2. Get order list\n\
                   3. Cancel an order\n\
                   4. Quit\n")
    action = action.strip()
    if not action.isdigit():
        raise BadInputException("Please enter a single integer value")
    try:
        return action_dict[action]
    except KeyError:
        raise BadInputException("Number must be between 1 and 4")


def single_add_query():
    """
    Gets list of items from user as a comma-separated string and passes it
    to client.
    Raises error if user input is not alphanumeric or more than 3 items are
    provided.
    """
    item_string = input("\nPlease enter items (Must Be separated by commas): ")
    # only alphanumeric input allowed
    test_re = re.compile(r"\s*(\w|\s)+(,(\w|\s)+){0,2}\s*")
    match = re.match(test_re, item_string)
    if match is None or len(match.group(0)) != len(item_string):
        raise BadInputException("You can only submit a maximum of 3 items\n\
                                N.B. all items must be alphanumeric\n\
                                i.e. no punctuation marks allowed (except comma, which is the item delimiter)")
    return item_string.strip()


def single_cancel_query():
    """
    Gets number of item to be deleted and passs it to the client.
    Raises an error if non-integer value provided
    """
    item_no = input("Please enter the item number: ")
    item_no = item_no.strip()
    if not item_no.isdigit():
        raise BadInputException("Please enter a single positive integer value")
    return str(int(item_no) - 1)


def print_response(response):
    print()
    if "items" not in response:
        print(response["message"], "\n")
    elif len(response["items"]) == 0:
        print("you have no items\n")
    else:
        item_string = "Here are your orders\n" + "\n".join(
            ["{}. {}".format(i + 1, e) for i, e in enumerate(response["items"])]
        )
        print(item_string, "\n")
