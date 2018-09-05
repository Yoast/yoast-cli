CLI tool we use at Yoast to automate some tasks.

## Features

* Generate changelogs based on milestoned issues.
* Create beta-versions of Yoast SEO with support for custom branches for YoastSEO.js and Yoast Components.
* More coming in the future.

## Setup

Before you can start using the script, it is necessary to register a GitHub access token. To do this, follow these steps:

* Generate a new Personal Access Token in GitHub by going [here](https://github.com/settings/tokens/new). Be sure to check the `repos` box under scope.
* Click `Generate token` and make sure you copy the newly generated access token.
* Clone this Wiki repository to your preferred directory.
* Navigate to `/yoast-cli/`
* Run `composer install` to install all necessary dependencies.
* Copy the `.env.example` file and rename it to `.env` by running `cp .env.example .env`.
* Open the file with your preferred editor and replace `your_token_here` with the access token you just generated. Save the file.

## Usage

After you've successfully setup the script, it's time to use it!

### General usage

Whenever you want to call one of the commands within the script, ensure you start off with `./yoast-cli.php` followed by a space and the wanted command. **Please note the `.` before the slash. This is required to properly run the command!**

When simply running `./yoast-cli.php`, you'll be presented with some information about the available commands and their usage. Go on, give it a try!

### Generating changelogs

If you want to generate changelogs for a particular repository, all you need to do is follow these steps: 

* Run `./yoast-cli.php changelog:create`. You'll be presented with a list of available repositories to generate a changelog for.
* Select the wanted repository. You'll be presented a list of open milestones. 
* Select the wanted milestone.
* The changelog items will automatically be collected and written to the `/changelogs/ directory.

### Creating beta versions (currently not usable for RCs)

**Please note that this currently only works for Yoast SEO due to some limitations in the setup of other repositories.**

If you want to beta version for a particular repository, all you need to do is follow these steps: 

* Run `./yoast-cli.php beta`. You'll be presented with a list of available repositories to generate a beta version for.
* Select one of the repositories.
* Supply what branch of the plugin you want to build (Required).
* Supply what branch of YoastSEO.js you want to build (Optional. Leave blank if you want to use the default).
* Supply what branch of Yoast Components you want to build (Optional. Leave blank if you want to use the default).
* Supply a name for the zip file.

On average, it takes about 15 minutes to create a beta if you have custom dependencies selected. If you just want to build a specific release branch, this can vary between ~8 and ~10 minutes.

## Good luck!

![](http://i.giphy.com/b8FBho6FFLsFG.gif)
