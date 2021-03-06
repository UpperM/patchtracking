<!--
*** Thanks for checking out this README Template. If you have a suggestion that would
*** make this better, please fork the repo and create a pull request or simply open
*** an issue with the tag "enhancement".
*** Thanks again! Now go create something AMAZING! :D
***
***
***
*** To avoid retyping too much info. Do a search and replace for the following:
*** github_username, repo, twitter_handle, email
-->





<!-- PROJECT SHIELDS -->
<!--
*** I'm using markdown "reference style" links for readability.
*** Reference links are enclosed in brackets [ ] instead of parentheses ( ).
*** See the bottom of this document for the declaration of the reference variables
*** for contributors-url, forks-url, etc. This is an optional, concise syntax you may use.
*** https://www.markdownguide.org/basic-syntax/#reference-style-links
-->

<!-- PROJECT LOGO -->
<br />
<p align="center">
    <img src="https://github.com/UpperM/patchtracking/blob/main/public/assets/img/PatchTracking-logo-text.png" alt="Logo">

  <p align="center">
    Manage application your updates from web application.<br>
    Retrieve updates status automatically from APIs
    <br />
    <a href="https://github.com/UpperM/patchtracking"><strong>Explore the docs </strong></a>
    <br />
    <br />
  </p>
</p>



<!-- TABLE OF CONTENTS -->
## Table of Contents

* [About the Project](#about-the-project)
  * [Pre-built API](#pre-built-api)
* [Getting Started](#getting-started)
  * [Prerequisites](#prerequisites)
  * [Installation](#installation)
* [Usage](#usage)
* [Roadmap](#roadmap)
* [Contributing](#contributing)
* [License](#license)
* [Contact](#contact)
* [Acknowledgements](#acknowledgements)



<!-- ABOUT THE PROJECT -->
## About The Project
<img src="https://github.com/UpperM/patchtracking/blob/main/screenshot.png" alt="Logo">

This application allows you to manage the updates of your applications. It's possible to automatically retrieve the installed version of the software (through the API) and the latest github release.

A changelog system allows you to keep the history of the updates. 

An integration with [GLPI](https://github.com/glpi-project/glpi) is possible to automatically open tickets, make follow-up and close it.

Authentication can be done locally or with LDAP.

### Pre-built API 

* [Grafana](https://grafana.com/)
* [WAPT](https://github.com/tranquilit/WAPT)
* [GLPI](https://github.com/glpi-project/glpi)
* [Kapacitor](https://github.com/influxdata/kapacitor)
* [Cachet](https://github.com/CachetHQ/Cachet/)
* [vSphere vCenter](https://www.vmware.com/products/vcenter-server.html)
* [Veeam](https://www.veeam.com/fr)
* [Owncloud](https://owncloud.org/)


<!-- GETTING STARTED -->
## Getting Started

To get a local copy up and running follow these simple steps.

### Prerequisites

* PHP >= 7.2 (PDO, MBstring, Tokenizer, GD, MySQL, SimpleXML & DOM, ldap, curl)
* MySQL >= 5.6
* Git Version Control
* [Composer](https://getcomposer.org/)



### Installation

Create database and user database

```sql
 CREATE USER 'user_patchtracking'@'localhost' IDENTIFIED BY 'myPassword';
 CREATE DATABASE patchtracking;
 GRANT ALL ON prod_patchtracking . * TO 'user_patchtracking'@'localhost';
 FLUSH PRIVILEGES;
 ```

Clone the repo
```sh
git clone https://github.com/UpperM/patchtracking/patchtracking.git --branch release --single-branch
```

``cd`` into the application folder and run ``composer install --no-dev``

Copy the ``.env.example`` file to ``.env`` and fill with your own database and mail details.

Ensure the storage, ``var/cache`` & ``public/uploads`` folders are writable by the web server.

In the application root, Run ``php artisan key:generate`` to generate a unique application key.

Set the web root on your server to point to the PatchTracking public folder. This is done with the ``root`` setting on Nginx or the ``DocumentRoot`` setting on Apache.

Run ``php bin/console doctrine:schema:create`` to update the database.

Done! You can now login using the default admin details ``admin@admin.com`` with a password of ``password``. You should change these details immediately after logging in for the first time.


<!-- USAGE EXAMPLES -->
## Usage

Use this space to show useful examples of how a project can be used. Additional screenshots, code examples and demos work well in this space. You may also link to more resources.

<!-- ROADMAP -->
## Roadmap

See the [open issues](https://github.com/UpperM/patchtracking/issues) for a list of proposed features (and known issues).



<!-- CONTRIBUTING -->
## Contributing

Contributions are what make the open source community such an amazing place to be learn, inspire, and create. Any contributions you make are **greatly appreciated**.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request



<!-- LICENSE -->
## License

Distributed under the MIT License. See `LICENSE` for more information.



<!-- CONTACT -->
## Contact

Upperm - [@UpperM](https://twitter.com/uppperm)

Project Link: [https://github.com/UpperM/patchtracking](https://github.com/UpperM/patchtracking)

<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[contributors-url]: https://github.com/UpperM/patchtracking/graphs/contributors
[stars-url]: https://github.com/UpperM/patchtracking/stargazers
[issues-shield]: https://img.shields.io/github/issues/UpperM/patchtracking.svg?style=flat-square
[issues-url]: https://github.com/UpperM/patchtracking/issues
[license-shield]: https://img.shields.io/github/license/UpperM/patchtracking.svg?style=flat-square
[license-url]: https://github.com/UpperM/patchtracking/blob/master/LICENSE.txt
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=flat-square&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/othneildrew
[product-screenshot]: https://puu.sh/Gi0vu/78e1690d4c.png

