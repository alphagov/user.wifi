# Documentation for user.wifi code

This is the documentation for the user.wifi backend infrastructure.  If you’ve found this from a search online for user.wifi, and would like to use the service [read this] () If you’d like to implement user.wifi at a site where you offer guest wifi [read this] (https://governmenttechnology.blog.gov.uk/2016/06/17/wi-fi-security-and-government-wide-roaming-solutions/).

# Table of contents

<!-- MarkdownTOC -->

- [Elevator pitch - user.wifi](#elevator-pitch---userwifi)
- [Backend architecture](#backend-architecture)
	- [Overview](#overview)
	- [Database](#database)
	- [API tier](#api-tier)
	- [RADIUS tier](#radius-tier)
	- [Commiting, building and releasing](#commiting-building-and-releasing)
	- [Debugging](#debugging)
	- [To-do \(features\)](#to-do-features)
	- [To-do \(build and management\)](#to-do-build-and-management)

<!-- /MarkdownTOC -->

<a name="elevator-pitch---userwifi"></a>
# Elevator pitch - user.wifi 

A secure guest wi-fi service for UK government buildings.

<a name="backend-architecture"></a>
# Backend architecture

<a name="overview"></a>
## Overview

User.wifi :
- Onboarding process
 - new guest wi-fi users can sign up by SMS, user.wifi creating and issuing a unique and unchanging user + password and storing these in a database
 - has a similar process for sponsored sign up by email
- accepts RADIUS requests from users attempting to join the user.wifi SSID in government buildings, and checks against the database
- additionally checks if the site has a 'snowflake' rule requiring additional log in requirements to be met, and notifys the user of these requirements by SMS
- Sites wishing to roll out user.wifi need to connect their APs or AP controllers to user.wifi  _Q: how? IPSEC tunnel? is this always the case? what does it terminate on? what automation exists_
 
To accomplish the above we have the following components:
- A database to store details of sites, users and passwords
- An API (RESTful) tier that talks to the database, making changes as required
- A RADIUS tier that APs use to connect to that talks to the API tier

<a name="database"></a>
## Database

<a name="api-tier"></a>
## API tier

<a name="radius-tier"></a>
## RADIUS tier

<a name="connectivity-tier"></a>
## Connectivity tier

<a name="Management-and-Development-tier"></a>
## Management and Development tier

[AWS account] (https://344618620317.signin.aws.amazon.com/console)

[SSH dev/test/management host] (ssh://admin@52.50.52.124)



<a name="commiting-building-and-releasing"></a>
## Commiting, building and releasing

<a name="debugging"></a>
## Debugging

<a name="to-do-features"></a>
## To-do (features)

<a name="to-do-build-and-management"></a>
## To-do (build and management)

## Processes

Site onboarding
Site decommissioning
Site IP address change
User phone number change
User password reset

# Further documentation

## Links to documentation for departments and end users

## Output of pentest

## Braindump

* sort out repo merge thingy
* add dan and gary as controbutors
* dev boxes clones and pulls from ali's account
* dev in aws , also assets,  admin@
* 52.50.52.124
* automate backups
* install jenkins
* send out system.md to uknot
* create readme.md

* cloudwatch
* some config is environment variables most in files
* 
