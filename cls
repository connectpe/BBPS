[33mcommit 5c4477c7c667ec38bd1b7fde239e4aa9e0a66fb8[m[33m ([m[1;36mHEAD[m[33m -> [m[1;32mAbhishekFrontend[m[33m, [m[1;31morigin/develop[m[33m)[m
Author: Ramanand <ramanand.rajput@connectpe.in>
Date:   Fri Feb 20 10:23:56 2026 +0530

    solve the conflict

[33mcommit decea8ae7cb7fac41cbb734662a0f2257c1e2a78[m
Merge: 0d6b4cf b637272
Author: Ramanand <ramanand.rajput@connectpe.in>
Date:   Fri Feb 20 10:14:13 2026 +0530

    Merge pull request #90 from connectpe/AbhishekFrontend
    
    Date : 19-02-2026
    
    abhishekforntend branch merge with the develop branch

[33mcommit b637272a8a492a4119fe8704c34ce9ca631b12b9[m
Merge: 1c8ecb7 0d6b4cf
Author: Ramanand <ramanand.rajput@connectpe.in>
Date:   Fri Feb 20 10:13:38 2026 +0530

    Merge branch 'develop' into AbhishekFrontend

[33mcommit 0d6b4cf8df1110f6ffca01606d7b24eef4a755b9[m
Merge: af052dc 8f8e9d9
Author: Ramanand <ramanand.rajput@connectpe.in>
Date:   Fri Feb 20 10:10:43 2026 +0530

    Merge pull request #89 from connectpe/RamanandBackend
    
    1. fix the transaction helper code for sending the callback 2. fix th…
    
    RamanandBackend branch merge with the develop branch

[33mcommit 8f8e9d9e103fc54440d6e14b529a90484b3fcc68[m
Merge: 15dc970 af052dc
Author: Ramanand <ramanand.rajput@connectpe.in>
Date:   Fri Feb 20 10:10:06 2026 +0530

    Merge branch 'develop' into RamanandBackend

[33mcommit af052dc8beaebb6becde8835c0812eae131d0c20[m
Merge: 2fb6e7b 3daf6a6
Author: Ramanand <ramanand.rajput@connectpe.in>
Date:   Fri Feb 20 10:06:41 2026 +0530

    Merge pull request #88 from connectpe/nilam21jan
    
    Nilam21jan merge with the develop branch

[33mcommit 3daf6a68101871b69492fff07d9c5d35ddee1576[m
Merge: 2bc2fbe 2fb6e7b
Author: Ramanand <ramanand.rajput@connectpe.in>
Date:   Fri Feb 20 10:06:14 2026 +0530

    Merge branch 'develop' into nilam21jan

[33mcommit 2fb6e7bb174e2695b3e819330471a1527adf8cfa[m
Merge: ac77e9d 1b69c0b
Author: Ramanand <ramanand.rajput@connectpe.in>
Date:   Fri Feb 20 10:01:53 2026 +0530

    Merge pull request #87 from connectpe/Sneha
    
    1. Optimize the commonController Code 2. optimize mobikwik all api code.
    
    merge the code of Sneha branch to the develop branch

[33mcommit 1b69c0b88a73b3a22e690f111606bb757e1e9922[m
Author: Sneha-Singh-17-3-25 <sneha.singh@connectpe.in>
Date:   Fri Feb 20 09:56:11 2026 +0530

    1. Optimize the commonController Code 2. optimize mobikwik all api code.

[33mcommit 1c8ecb7d5e4029e0d144cdfcd2121d3f5f4cfe5a[m[33m ([m[1;31morigin/AbhishekFrontend[m[33m)[m
Author: Abhishek Chauhan <abhishek.chauhan@connectpe.in>
Date:   Thu Feb 19 17:33:28 2026 +0530

    Date : 19-02-2026
    1. Added the try- catch block for proper error handling for the below methods :
        1.1. index() - SchemeController.php
        1.2. UserassigntoSupport() - AdminController.php
        1.3. nsdlPayment() - AdminController.php
        1.4. supportBasedUserList() - AdminController.php
        1.5. index() - LadgerController.php
        1.6. enabledServices() - ServiceRequestController.php
        1.7. disableUserService() - AdminController.php
        1.8. addService() - AdminController.php
        1.9. UserStatusChange() - AdminController.php
        1.10. deleteAssignedScheme() - AdminController.php
        1.11. addSupportMember() - AdminController.php
        1.12. editSupportMember()- AdminController.php
        1.13. defalutSlug()- AdminController.php
        1.14. getProvidersByService()- AdminController.php
        1.15. index()- HomeController.php
        1.16. store()- ServiceRequestController.php
        1.17. generateMpin() - UserController.php
    
    2. Made the code customization like when fetch the record select only required column in below paged.
        2.1. Scheme Page.
        2.2. NSDL payment Page.
        2.3. Ladger Page.
        2.4. Admin/User/Api_Partner Profile page.
        2.5. Service Page in Api_Partner.
        2.6. Complain report page in Admin/Support Panel.
    
    3. Made the changes in Middleware : User and Api_Partner will have permission to update their profile.
    4. Shown the Complete Profile button in Api_Partner profile page.
    5. In Our_Service/Provider Page added the code when sweetalert is confirmed then change the toggle status.
    6. Removed some duplicate routes.
    7. Corrcted the list of the IP White list for the Api_Partner login.
    8. Hightlighted the error field when error comes in Complete Profile.
    9. Saved the Data on browser locally when user goes for complete profile : If user fills some steps in Complete profile and close the modal then filled data will be saved locally.
    10. Set the Local data user wise : When two user login which has no business details : and If already local data set in browser then it was coming same data for both user.
    11. After making this changes tested the complete profile module is working proper or not.

[33mcommit 2bc2fbe56acc23ebc23ddc519028db7e559e2253[m
Author: Ramanand <ramanand.rajput@groscope.com>
Date:   Thu Feb 19 17:32:36 2026 +0530

    change web.php file

[33mcommit 15dc970bbcb527ac4d583fc476acc02e9414c22b[m
Author: Ramanand <ramanand.rajput@connectpe.in>
Date:   Thu Feb 19 17:12:18 2026 +0530

    1. fix the transaction helper code for sending the callback 2. fix the email varification balde page for send the otp in mail 3. fix the web routes means rearrage the route in better way

[33mcommit 02e8c32cbe5283ba572f404ee50798e0dc87d3cb[m
Author: Ramanand <ramanand.rajput@groscope.com>
Date:   Thu Feb 19 14:41:50 2026 +0530

    verifyOtp , login, logout, checkUserStatus function keep in try and catch

[33mcommit 9db477a4ece4bcc8bf462d4ccc18e408fd0ecd48[m
Merge: 6e5f778 218c1da
Author: Ramanand <ramanand.rajput@groscope.com>
Date:   Thu Feb 19 14:13:55 2026 +0530

    Merge branch 'nilam21jan' of https://github.com/connectpe/BBPS into nilam21jan

[33mcommit 6e5f778fa175da30e4fb834c3972c82e159c8bbf[m
Author: Ramanand <ramanand.rajput@groscope.com>
Date:   Thu Feb 19 13:32:16 2026 +0530

    fix support user one ccolumn name and also
    correct api-partner recharge-report route name in sidebar
    and correct route

[33mcommit ac77e9dc60cf241de7db188a1a33f8217fae1ffd[m
Merge: 4b44610 cb4b83f
Author: Ramanand <ramanand.rajput@connectpe.in>
Date:   Thu Feb 19 10:23:24 2026 +0530

    Merge pull request #86 from connectpe/RamanandBackend
    
    fix the debitbalance job merge with develop

[33mcommit cb4b83fa2eb4138c2276b36d8aa2909b2c936a8c[m
Author: Ramanand <ramanand.rajput@connectpe.in>
Date:   Thu Feb 19 10:20:18 2026 +0530

    fix the debitbalance job

[33mcommit 4b446103075962b35084df3ddf02e58760d7dfb9[m
Merge: eb98655 218c1da
Author: Ramanand <ramanand.rajput@connectpe.in>
Date:   Thu Feb 19 10:14:58 2026 +0530

    Merge pull request #85 from connectpe/nilam21jan
    
    Nilam21jan merge with the develop

[33mcommit 218c1daa7f7b1e3107c8d1596d71de56967430b9[m
Merge: 6daef0f eb98655
Author: Ramanand <ramanand.rajput@connectpe.in>
Date:   Thu Feb 19 10:14:38 2026 +0530

    Merge branch 'develop' into nilam21jan

[33mcommit eb98655c415192fec9564594c1c78b4f98ba5105[m
Merge: 3c873ef 03883ed
Author: Ramanand <ramanand.rajput@connectpe.in>
Date:   Thu Feb 19 10:06:06 2026 +0530

    Merge pull request #84 from connectpe/AbhishekFrontend
    
    Abhishek frontend merge with develop

[33mcommit 03883ed24beb87cd1cfb76b6600925988460f833[m
Merge: a531aba 3c873ef
Author: Abhishek Chauhan <abhishek.chauhan@connectpe.in>
Date:   Wed Feb 18 18:20:30 2026 +0530

    Date : 18-02-2026
    1. Added the try- catch block in TransactionController.php for proper error handling for the below methods :
        1.1. transactionStatusCheck()
        1.2. transactionComplaint()
        1.3. complaintStatus()
        1.4. checkComplaintStatus()
        1.5. downloadInvoice()
        1.6. ourService() - ServiceController.php
        1.7. providers() - AdminController.php
        1.8. After Making the above changes tested above modules.
    
    2. Made the code customization like when fetch the record select only required column in below paged.
        2.1. Compaint Page in Us