#Group project: Plan of Action
##Preamble
Hi guys! This is the first prototype of what I feel the website
should look like (Based on the designs made last week courtesy
of Luke and Clare).
Considering It took me a whole week just to make a rough mock-up
of the home-page, I figured it was about time I brought you guys
into this :smiling_imp:.

Below is the list of more specific tasks that I presume building
the site will entail. It would be nice if we can put a name on 
each task so people have a clearer idea of what they need to do
(Chances are it will also help with the contribution matrix we
have to fill in at the end of the term).

I'd also recommend you have a think about which tasks you're
best suited to/ more interested in doing as it would
speed up the process of allocating the tasks on Monday.

**N.B. at the end of the document, you should find a 
proposal on how we push code to the repository, 
though this is open to discussion and I will gladly
consider any "better" alternatives**

##Task List 

Actual TODO:

Search (templates are bad, map popups can be improved)

Profile needs just a little touching up

Listing page is  broken

Or just ctrl+f 'todo' and 'TODO', they're everywhere

Old TODO:


* Refactoring the code:
    * [ ] find a way to **reduce code duplication** (e.g. common items
    such as the navbar and sidebar will be used on several pages).
    I've read briefly about this and php's include function seems
    like the way to go but feel free to come up with better 
    suggestions
    
* 'Backend' features:

    * [x] Location
    * [ ] Add email verification functionality (haha)
    * [x] Setup common access to database (We discussed putting it on AWS).
    * [ ] Link Search bar to database
    * [x] get relevant listings from database based on user location
    * [ ] Design user ranking system (Not particularly urgent since its not a basic deliverable) (nope)
    * [x] Create account
    * [x] Log in to account
    * [x] Editing username, allergies etc
    * [x] Build Database (Duh!)

* Dynamic/'Frontend' features:
    * [x] Displaying login errors somewhere (login errors are returned to index.php through the 'error' GET value)
    * [x] Styling for profile.php
    * [x] Changing navbar when user logs in (i.e Change login icon to
        profile icon and dropdown list with links to profile page,
        add listing e.t.c)
    (see http://www.w3schools.com/php/php_form_validation.asp)
    * [x] Getting user location information so 'items near you feature'
    is relevant
    * [x] Redirecting users to login/create a new account if they try
    to make a listing while not logged in/registered
    * [ ] Allow Social Sign-in : i.e link to Facebook/Google account
    * [x] Changing navbar when user logs in (right now it just says hi and logs them out onclick)
    * [x] Set up form validation for user input
    
* Website Design:
    * [ ] Design a color scheme/font scheme for the website (consistency in
    styling between pages)
    * [x] Design the logo for the website (Probably need to decide on a name
    first)
    
* Improve mobile support compatibility:
    * [x] stop sidebar from distorting web content (probably by 
    floating over it rather than shifting all the content to the
    side)
    * [x] keep sidebar fixed when you scroll through main-page content
    (this is also an issue on desktops)
    
* Making Additional Pages/features (Will inevitably lead to 
adding more tasks to the list once identified):
    * Messaging functionality
    * User Profile page:
        * User can manage their personal info and listings
        * Users should be able to view their past listings
        (As described in the minimum deliverables)
    * Search Results Page:
        * Search result filters (i.e. by Location, Food Category)
        * Sorting of search results (i.e. by distance, quantity 
        of food, expiry dates)
        * Live google maps showing location of listings (Advanced 
        Deliverable)
    * Checkout Page:
        * Users can edit their baskets
        * Contact sellers regarding items?
    * About/Contact us Page:
        * A system for managing user feedback?
        * Share our site on social networks i.e. twitter/facebook

##Repository management
I thought it might be best if each person pushes their feature
onto a new "feature" branch, then one person is responsible
for merging these features onto the **develop** branch.

1. to create a new branch:
    `git branch <branch-name>`

>N.B You should run this command while on the branch you 
>to "branch off" from

2. to switch to that branch: `git checkout <branch-name>`

3. To push the branch to the online repo:
   `git push origin <branch-name>`

>N.B. if you rename your remote then its 
>`git push <remote-name> <branch-name>`

Also, if you'd like to update your local repository with changes 
from the remote, type

 `git fetch origin`
 
 to fetch the updates, then
 
 `git merge origin/<branch-name>`
 
 to merge the remote repository changes into local branch
 
>Alternatively you could type `git pull origin <branch-name>`
>to automatically fetch and merge the remote
