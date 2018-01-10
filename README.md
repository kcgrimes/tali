## Team Administration/Logistics Interface

The Team Administration/Logistics Interface, or TALI, is a Content Management System intended to provide unique functionality while remaining user friendly and compatible. User content is managed by a number of Modules that act as Graphical User Interfaces (GUI) in order to add, update, and remove content stored on a MySQL database handled by the user. Additionally, TALI contains multiple features tailored to organization and team management.

This User Manual is intended to provide detailed information as to the purpose, function, FAQs, and minor troubleshooting for each of TALI’s Modules in addition to installation, uninstallation, and maintenance tips. For further information or specifics in the code, the user should read the comments to the code within TALI’s various files. 

## Author Information

Kent “KC” Grimes of Austin, Texas, United States is the author of the Team Administration/Logistics Interface (TALI), an online Content Management System (CMS) created in 2014 intended to support websites in a user-friendly manner while still leaving the door open for future expansion via the user’s unique ideas. 

The point of this CMS is to rely a little more on functionality and less on interface limitations, which is a step in a different direction from most CMS’s. Although this CMS may call for more understanding of website design than mainstream CMS’s, it will provide a more functional and more rewarding experience. The key is TALI’s ability to work seamlessly with other major installations in order to take advantage of its modules and functionality without the concern for compatibility and front-end appearance. This ultimately gives the user more freedom while developing their website.

Contact E-Mail: kc.grimes@gmail.com

## Installation

For now, TALI is only hosted on github at: https://github.com/kcgrimes/tali  

At this time, there is no “installer” for TALI, and it is instead a series of folders and files that require placement. 

1. Obtain necessary files (available at https://github.com/kcgrimes/tali)
	1. “tali” folder
	1. config.ini.tmpl
	1. tali_init.php.tmpl
	1. MySQL database tables (.sql file)
2. Place the “tali” folder and the tali_init.php.tmpl file somewhere in the root directory of the website (/public_html, /www, /dev, etc.)
	1. It does not have to be in the top level of the root, it could be in any folder in that root. Ex: /public_html, /public_html/cms, etc.
3. If you choose to use it, place config.ini.tmpl in your web directory, preferably outside of the root directory for security purposes
	1. Also rename it to config.ini
4. For any page that may call on TALI or in a file that is always executed (defines.php, head.php, global.php, etc.), place the following line of PHP code towards the beginning of the page’s execution. Ideally this is just after where your <head> tags occur (where <title> and <meta> are defined, shortly after session_start(), etc.)

`//Initialize Team Administration/Logistics Interface (TALI)
require "tali_init.php";`	
	
Note: If the tali_init.php file is anywhere but the root directory (or whatever level <head> is in) you will need to adjust the path above accordingly, along with make adjustments as directed in tali_init.php.

5. Using phpMyAdmin or similar MySQL Database GUI, import the TALI .sql file to the desired database so that the TALI tables will populate
6. Rename tali_init.php.tmpl to tali_init.php
7. Edit tali_init.php to tailor TALI to your website’s configuration
8. At this time, the user should not notice any changes to their website unless there are conflicting CSS definitions. The user should however be able to access /tali/index.php (Ex. https://www.domain.com/tali/index.php) and begin utilizing the modules!
	1. The initial access login is temporary and should be changed to a permanent, unique login ASAP:
		a. Username: admin
		a. Password: password

## Documentation

A Word document is included with TALI. This User Manual is intended to provide detailed information as to the purpose, function, FAQs, and minor troubleshooting for each of TALI’s Modules in addition to installation, uninstallation, and maintenance tips. For further information or specifics in the code, the user should read the comments to the code within TALI’s various files. Any further questions or comments can be directed to the author. 

## Tests

TALI is designed to exit upon critical failure and it will attempt to announce the problem in plain text. These types of failures are intended for development, and should never be encountered down the road if they were not encountered at launch, save for software updates. Upon setup or completion of modifications, it is recommended that the user, before launch, access at minimum the following pages:
* Home Page
* Some other front-end page that calls TALI
* Front-end page that uses a heavy TALI module (Roster, News, etc.)
* Back-end TALI Index
* Back-end page for any TALI module

## Contributors

Contributions are welcomed and encouraged. Please follow the below guidelines:
* Use the Pull Request feature
* Document any additional work
* Provide reasonable commit history comments
* Test all modifications locally and online

## License

MIT License

Copyright (c) 2014-2018 Kent "KC" Grimes. All Rights Reserved.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
