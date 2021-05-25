# Coil Web Monetization

This is the source code repository for the Coil WordPress plugin. Coil Web Monetization allows you to monetize content with Public Access, Coil Only Access, or if you are using modern WordPress, at the block level with Split Access.

---

* [ZenHub Coil WordPress Plugin Workspace Workflow](#markdown-header-zenhub-coil-wordPress-plugin-workspace-workflow
)
* [Local Development Environment](#markdown-header-local-development-environment)
* [Development Process](#markdown-header-development-process)
* [Deployment](#markdown-header-deployment)

---
## ZenHub Coil WordPress Plugin Workspace Workflow

Coil uses ZenHub to manage our WordPress Plugin workflow. To use please install the 
[ZenHub extension](https://zenhub.com/extension) and then login to it with 
GitHub OAuth. Then click request access so we can approve your license.  

After that you will be taken to the workspaces board where you should search for
the "WordPress Plugin" workspace. 

On the "WordPress Plugin" workspace issues move from left to right on the board. 

Intake Pipelines (New Issues|New Bugs) -> Queue -> Up Next -> In Progress -> Closed

All new issues go into one of the following **Intake** pipelines: 

| New Issues | Triage (Bugs)|
| --- | --- |
| Any issue which is not a bug or a feature | New Bugs reported. Label: "Type: Bug"

The team reviews these issues. If they are valid bugs, a new feature we want to implement, or an issue we wish to work on we move it into the **Queue**. 

The **Queue** is a prioritized list of issues the team plans to work on. 

From the **Queue** issues are pulled into the **Up Next** pipeline by the Coil Team. Anyone works on these issues and they then move through the **In Progress** and then to the **Closed** pipeline once work is completed. 

# Local Development Environment

It's recommended that you use [Local by Flywheel](https://localbyflywheel.com/) for your local development environment.

The nature of this project (a plugin) means that it is environment-agnostic, so if you prefer to install and administer WordPress using another method, then please do so.

## Setting up the Development Environment using Flywheel

Ensure you have the prerequisite software installed:

* [PHP](https://php.net/) 7.2+
* [Composer](https://getcomposer.org/) 1.8+, installed globally.
* [Local By Flywheel](https://localbyflywheel.com/community/t/local-by-flywheel-3-3-0/13527) 3.3.0
* [Node](https://nodejs.org/) 12.0.0
* An account on [Coil.com](https://coil.com/).

Install the development environment:

1. Set up a new site in Flywheel:
	- Name the new site `coil`.
	- Choose the latest PHP version available.
	- This project does not require a specific Web Server or MySQL version; if in doubt, choose nginx and MySQL 5.6.
1. After Flywheel has created the site, from inside the root of where you chose to create the site, clone this repository into the plugins folder (make sure to add SSH key to Github plugin repo):
	- `git clone git@github.com:coilhq/coil-wordpress-plugin.git app/public/wp-content/plugins/coil-web-monetization`
1. When the machine has finished provisioning, install the development dependencies:
	- `cd app/public/wp-content/plugins/coil-web-monetization && composer install && npm install`
1. To configure Flywheel for PHPUnit:
	- Download the following script to the base of your`app/` folder:
		- `curl -o setup-phpunit.sh https://gist.githubusercontent.com/keesiemeijer/a888f3d9609478b310c2d952644891ba/raw/`
	- In Flywheel, right-click your "Coil" site and select "Open Site SSH". This will open a new terminal, and inside it, do: `bash /app/setup-phpunit.sh`
	- The script wil take several minutes to install PHPUnit. Once it's done, you can close the terminal.

There is no sample database to import.

Flywheel will give you the URL of where you can access your environment, and you'll have supplied the initial user account's username and password during Flywheel set up.


# Development Process

The development process follows [Git Flow](http://jeffkreeftmeijer.com/2010/why-arent-you-using-git-flow/).

Development happens in feature branches, which are merged into `develop`, and then eventually merged into `main` for deployment to production. When making changes, a feature branch should be created that branches from `develop` (and the corresponding merge request on Github should use `develop` as the target branch).

## Plugin Structure

This plugin maintains a basic file structure:

* `plugin.php` – The main plugin file.
* `includes/` – The plugin's backend/PHP code.
* `assets/` – Directory containing any static assets:
	* `css/` – Compiled CSS to be served to the frontend.
	* `fonts/` – Web fonts, if any.
	* `images/` – Images.
	* `js/` – JavaScript files.
	* `scss/` – SCSS source files.
* `README.md` – Plugin developer readme.

Within the `includes/` directory, files should be organised hierarchically based on namespaces. If a namespace only contains functions, the filename should be `functions.php`.

If a namespace also contains classes, group those classes together with the namespace file in a `{sub-namespace}/` directory. The main functions file for this namespace would be`{sub-namespace}/functions.php`, while classes should be in a file prefixed with `class-` with a "slugified" class name.

This plugin also has some complex JavaScript frontends, so there is additional structure to better match the ecosystem tooling:

* `src/` – Source JavaScript and CSS files.
* `build/` – Built assets, typically generated by Webpack and Babel

## Assets (CSS & JS)

Some of the tooling for this plugin came from the `create-guten-block` project, so we have two different ways to manage CSS and JS.

### Block Assets (`src/`)
#### `npm start`
- Use to compile and run the block in development mode.
- Watches for any changes and reports back any errors in your code.

#### `npm run build`
- Use to build production code for your block inside the `dist/` folder.
- Runs once and reports back the gzip file sizes of the produced code.

### Other Assets (`assets/{js,scss}/`)
#### `npx grunt watch`
- Use to compile and run the assets in development mode.
- Watches for any changes and reports back any errors in your code.

#### `npx grunt build`
- Use to build production code for your assets inside the `assets/{css,js}` folders.

## Integration Tests
New tests should be written where it makes sense. With Flywheel, the PHPUnit tests have to be run inside the Flywheel environment. To do this:

- In Flywheel, right-click your "Coil" site and select "Open Site SSH". This will open a new terminal.
- Inside it, change into the plugin folder: `cd /app/public/wp-content/plugins/coil-web-monetization`.
- To run all tests, simply do: `phpunit`.

At this moment in time, the tests are not run automatically on public CI services.

## Cypress Tests
To run the tests you will need to: 

- Make sure all steps under `Setting up the Development Environment in Flywheel` have been completed
- Replace your existing WP database with this test snapshot, found in `/tests/cypress/fixtures/test-database.sql`
- You can do this in you SQL gui of choice but to do it in adminer (found in the database tab of Flywheel) go to import, then select the `test-database.sql` file and click import
- `npx cypress run` - to run the tests in the CI
- `npx cypress open` - to run the tests with a gui

# Installing plugin

- for testing, the Github repo can be zipped up and the zip used to install the Coil plugin on WordPress
	- on Github, go to the front page and click on "Clone or download"
	- choose "Download ZIP"
- for production, use command line `grunt zip` to create a zip in the `/resources/` folder. This removes development files such as composer.json and the tests folder.


# Production Build
 The zip and the tag are different builds -- the tag is obviously the source version, and the zip has some build files removed. So, to generate the versions you'll want to distribute:
1. `composer install && npm install`
1. `npm run build && npx grunt build`
1. If you want to change the version number, update it in package.json and run npx grunt version
1. (commit changes from above steps
1. `npx grunt zip` -- it will make a zip and put it in the releases/ folder.
1. Take that zip unzip the file and commit it to SVN that way and then get it onto WordPress.org Plugins SVN.
