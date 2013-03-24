# Server dashboard

This is a simple and awesome dashboard for your own server. The dashboard require the functionality of shell_exec - they are mostly disabled on shared hosting systems. There is also a simple cache functionality included to prevent high loads on the system; the cache time can be setted in the top of the `assets/getStatus.php` file.

JSON will be used to transfer data between the dashboard and the PHP script. Optionally you can allow external access from other domains with AJAX to the simple json-API - simply change `$AllowExternalAccess = false;` to `$AllowExternalAccess = true;`.

### Requirements
---
1. a (own?) linux machine (Debian recommended).
2. PHP 5.3+.
3. a browser with CSS3 support.
4. some basic knowledge.

### Screenshot
---
![Screenshot 1](https://raw.github.com/patschi/serverdashboard/master/ServerOverview1.png "Screenshot 1")
![Screenshot 2](https://raw.github.com/patschi/serverdashboard/master/ServerOverview2.png "Screenshot 2")
![Screenshot 3](https://raw.github.com/patschi/serverdashboard/master/ServerOverview3.png "Screenshot 3")

### Live Demo
---
Live demonstration can be viewed on <a href="http://serverdashboard.pkern.at" target="_blank">pkern.at</a>


### Credits
---
Awesome design by <a href="http://code-project.de" target="_blank">code-project.de</a><br />
Code and idea by <a href="http://pkern.at" target="_blank">pkern.at</a>