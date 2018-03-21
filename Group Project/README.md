# How to clone a Github repository to your machine 

By Soumya Singh

**Acknowledgements: Alistair Madden, the Compsoc President, for this detailed explanation**

An application to alleviate food wastage by allowing people to 'donate' unwanted food to the needy.

1. The first step is to make a folder to clone the repository:

 **mkdir directory_name** 
 
 **cd directory_name**

2. Then clone the repository:

  **git clone https://github.com/athenexcalibur/gp8.git gp**
  
    Install git from **https://git-scm.com/downloads** if you don't have it.

3. This will place a folder called **gp** inside your **directory_name** folder.

4. Navigate to the project folder:
  
   **cd directory_name**
  
   You may check the contents of the directory by typing **ls** in bash (Mac/Linux) or **dir** (Windows).

   The branch already checked out is the **master** branch. This must always contain the latest/final release.

   The developer branch is where all new features are all branched from and merged into - you can refer to http://nvie.com/posts/a-successful-git-branching-model/

5. To view a local branches, you use the **checkout** command. Say, a branch is called **indexpage**. You should type:

   **git checkout indexpage**

   To view all local branches on your machine, type **git branch**

6. If you have followed these instructions, you should only see the **master** branch. Type **git checkout develop** to create the **develop** branch.
   This is a **local** branch (the one on your machine) which is linked to the **remote** branch (the one online on Github).
   
7. Type **ls** or **dir** once more. This will highlight all changes made in files.

For more information, check-out https://git-scm.com/doc



