# REST API Client
This project is a raw PHP (7.4+) Supermetrics REST API client project.
This is a sample project for fetching and manipulating JSON data from a fictional Supermetrics Social Network REST API.
Developed an object-oriented code, especially considering design thinking to be generic, extendable, easy to maintain by
other staff members while thinking about performance.

No third party libraries were used on this project. Used PHP cURL to connect and communicate with the Supermetrics API.
I have used curl_multi that allows the processing of multiple cURL handles asynchronously. I was able to reduce the run time by ~90% using curl_multi for concurrent requests for 10 pages.

# Requirements

 * PHP 7.4 or higher needs to be installed.
  ```
 sudo apt-get install php
 ```
 * PHP cURL library must be installed.
 ```
 sudo apt-get install php-curl
 ```

# Installation

 1. Clone or download this repository
 ```
 git clone https://github.com/DuminduP/Rest-API-client.git
 ```
 2. Change directory
 ```
 cd rest-client/
```
3. Install phpunit
```
sudo apt install phpunit
```
4. Run unit tests
```
phpunit
```
5. Run sample script to see the summary report.
```
php summary.php
```
