#!/usr/bin/env bash

# ===============================================================================
# Script to install PHPUnit in the Circle CI environment
# Adapted from original source curl -o setup-phpunit.sh https://gist.githubusercontent.com/keesiemeijer/a888f3d9609478b310c2d952644891ba/raw/
# These packages are installed
# 
#     PHPUnit, wget, rsync, and subversion.
# 
# The WordPress and WP Test Suite paths are created as global variables and the respetive folders are created.
# WordPress is installed in the `/tmp/wordpress` directory for use by PHPUnit. 
# The WordPress test suite is installed in the `/tmp/wordpress-tests-lib` directory.
# 
# That way plugins can make use of them for unit testing. Plugins that have their
# tests scaffolded by WP-CLI also makes use of them. 
#
# Use options to install specific versions for PHPUnit, WordPress or the WP_UnitTestCase.
# ===============================================================================


# ===============================================================================
# Options to use in config.yml file
# 
# Without options this script installs/updates PHPUnit, WordPress and the WP test suite.
# 
# Install a specific PHPUnit version with the --phpunit-version option.
# 
#     bash setup-phpunit-ci.sh --phpunit-version=7
# 
# Install a specific WordPress version with the --wp-version option. This option
# accepts a version number, 'latest', 'trunk' or 'nightly'. Default 'latest'
# 
#     bash setup-phpunit-ci.sh --wp-version=5.0
# 
# Install a specific WordPress Test Suite with the --wp-ts-version option. This option
# accepts a version number, 'latest', 'trunk' or 'nightly'. Default 'latest'
#     
#     bash setup-phpunit-ci.sh --wp-ts-version=trunk
# 
# Update all packages (wget, etc) installed by this script with the --update-packages option.
# 
#     bash setup-phpunit-ci.sh --update-packages
# 
# Use the --help or -? option to see more information about this script.
# 
#     bash setup-phpunit-ci.sh --help
# 
# ===============================================================================


# ===============================================================================
# Default PHPUnit version
# 
# PHPUnit version 7 is installed for PHP version 7.1 and above
# PHPUnit version 5 is installed for PHP version 7.0
# PHPUnit version 4 for all other PHP versions
# 
# Run the command with a version if you need to test with a specific PHPUnit version
# 
# bash setup-phpunit-ci.sh --phpunit-version=7
# This example will install the latest PHPUnit from version 7 (e.g. 7.5.3)
# 
# Available PHPUnit versions can be found here.
# https://phar.phpunit.de
# 
# PHPUnit versions compatible with PHP versions can be found here.
# https://phpunit.de/supported-versions.html
# 
# ===============================================================================


# Strings used in error messages.
readonly QUIT="Stopping script..."
readonly CONNECTION="Make sure you're connected to the internet."
readonly RED='\033[0;31m' # Red color.
readonly RESET='\033[0m' # No color.

# Functions
function download() {
	download=false
	if wget --spider "$1" >/dev/null 2>&1; then
		wget -q --show-progress -O "$2" "$1" && download=true

		# Check if file exists.
		if [[ -f "$2" && "$download" = true ]]; then
			return 0
		fi
	fi

	printf "${RED}WARNING${RESET} Could not download %s %s\n" "$1" "$CONNECTION"
	return 1
}

function download_test_suite() {
	local exit=0
	if wget --spider "https://develop.svn.wordpress.org/$1/tests/phpunit/includes/" >/dev/null 2>&1; then
		svn export --quiet --force "https://develop.svn.wordpress.org/$1/tests/phpunit/includes/" "/tmp/tmp-wordpress-tests-lib/includes/"
		svn export --quiet --force "https://develop.svn.wordpress.org/$1/tests/phpunit/data/" "/tmp/tmp-wordpress-tests-lib/data/"
		svn export --quiet --force "https://develop.svn.wordpress.org/$1/wp-tests-config-sample.php" "/tmp/tmp-wordpress-tests-lib/wp-tests-config.php"
		for path in includes data wp-tests-config.php; do
			# Check if path exists.
			[[ ! -e "/tmp/tmp-wordpress-tests-lib/$path" ]] && exit=1 
		done
		if [[ 0 = "$exit" ]]; then
			return 0
		fi
	fi

	printf "${RED}WARNING${RESET} Could not download %s Test Suite. %s\n" "$2" "$CONNECTION"
	return 1
}

function packages_installed() {
	for file in /usr/bin/wget /usr/bin/svn /usr/bin/rsync; do
		# Check if executable file.
		if ! [[ -f "$file" && -x "$file" ]]; then
			return 1
		fi
	done
	return 0
}

function clean_up_temp_files() {
	# Clean up files added by this script.
	[[ -d "/tmp/tmp-wordpress/" ]] && rm -rf "/tmp/tmp-wordpress/"
	[[ -d "/tmp/tmp-wordpress-tests-lib/" ]] && rm -rf "/tmp/tmp-wordpress-tests-lib/"
	[[ -f "/tmp/my.cnf" ]] && rm -f "/tmp/my.cnf"
}

function exit_script() {
	clean_up_temp_files
	exit 1
}

# Get arguments.
for arg in "$@"
do
	if [[ "$arg" =~ ^- ]]; then
		# Argument start with a dash.
		case "$arg" in
			--phpunit-version=*) PHPUNIT_VERSION=${arg#"--phpunit-version="};;
			--wp-version=*) WP_VERSION=${arg#"--wp-version="};;
			--wp-ts-version=*) WP_TS_VERSION=${arg#"--wp-ts-version="};;
			--update-packages*) UPDATE_PACKAGES=true;;
			-?|--help)
				printf "Install PHPUnit in the Local by Flywheel Mac app\n\n"
				printf "Usage:\n"
				printf "\tbash setup-phpunit-ci.sh [option...]\n\n"
				printf "Example:\n"
				printf "\tbash setup-phpunit-ci.sh --phpunit-version=6 --wp-version=trunk\n\n"
				printf "Options:\n"
				printf -- "\t--phpunit-version    PHPUnit version to install\n"
				printf -- "\t--wp-version         WordPress version to install\n"
				printf -- "\t                     Accepts a version number, 'latest', 'trunk' or 'nightly'. Default 'latest'\n"
				printf -- "\t--wp-ts-version      WordPress Test Suite version to install\n"
				printf -- "\t                     Accepts a version number, 'latest', 'trunk' or 'nightly'. Default --wp-version option\n"
				printf -- "\t--update-packages    Update all packages installed by this script\n"
				printf -- "\t                     Updates wget, rsync, and subversion\n"
				printf -- "\t-?|--help            Display information about this script\n\n"
				exit 0
			;;
			*)
				printf "Unknown option: %s.\nUse \"bash setup-phpunit-ci.sh --help\" to see all options\n%s\n" "$arg" "$QUIT_MSG"
				exit_script
			;;
		esac
	else
		# Argument doesn't start with a dash.
		printf "Unknown option: %s.\nUse \"bash setup-phpunit-ci.sh --help\" to see all options\n%s\n" "$arg" "$QUIT_MSG"
		exit_script
	fi
done


INSTALL_PACKAGES=false
if ! packages_installed; then INSTALL_PACKAGES=true; fi
[[ -z "$UPDATE_PACKAGES" ]] && UPDATE_PACKAGES=false

if [[ "$INSTALL_PACKAGES" = true || "$UPDATE_PACKAGES" = true ]]; then

	[[ "$INSTALL_PACKAGES" = true ]] && printf "Installing packages...\n" || printf "Updating packages...\n"

	# Re-synchronize the package index files from their sources.
	apt-get update -y

	# Install packages.
	apt-get install -y wget subversion rsync
fi

# Re-check if all packages are installed.
if ! packages_installed; then
	printf "${RED}ERROR${RESET} Missing packages. %s\n%s\n" "$CONNECTION" "$QUIT"
	exit_script
fi

# Get the current PHP version.
PHP_VERSION=$(php -r "echo PHP_VERSION;")

# Get first three characters from version
readonly PHP_VERSION="${PHP_VERSION:0:3}" 

# Set the PHPUnit version if needed.
if [[ -z "$PHPUNIT_VERSION" ]]; then
	case "$PHP_VERSION" in
	7.4|7.3|7.2|7.1)
		PHPUNIT_VERSION=7
		;;
	7.0)
		PHPUNIT_VERSION=5
		;;
	*)
		PHPUNIT_VERSION=4
		;;
	esac
fi

readonly PHPUNIT_VERSION="$PHPUNIT_VERSION"

# Install PHPUnit.
printf "Installing PHPUnit %s... \n" "$PHPUNIT_VERSION"
if download "https://phar.phpunit.de/phpunit-$PHPUNIT_VERSION.phar" "phpunit-$PHPUNIT_VERSION.phar"; then
	chmod +x "phpunit-$PHPUNIT_VERSION.phar"
	mv "phpunit-$PHPUNIT_VERSION.phar" /usr/local/bin/phpunit
else
	printf "%s\n" "$QUIT"
	exit_script 
fi

# Test WordPress environment variables.
export WP_CORE_DIR=/tmp/wordpress
export WP_TESTS_DIR=/tmp/wordpress-tests-lib
if [[ -z "$WP_CORE_DIR" || -z "$WP_TESTS_DIR" ]]; then
	printf "${RED}ERROR${RESET} The WordPress directories for PHPUnit are not set\n%s\n" "$QUIT"
	exit_script
fi

# Delete tmp files (if they exist).
clean_up_temp_files

# Create tmp directories.
mkdir "/tmp/tmp-wordpress/" || exit
mkdir "/tmp/tmp-wordpress-tests-lib/" || exit

# Create core and tests directories (if needed).
[[ -d "$WP_CORE_DIR" ]] || mkdir "$WP_CORE_DIR" || exit
[[ -d "$WP_TESTS_DIR" ]] || mkdir "$WP_TESTS_DIR" || exit

cd "$WP_CORE_DIR" || exit

# Set default WordPress version.
[[ -z "$WP_VERSION" ]] && WP_VERSION='latest'

# Get the latest WordPress version from API.
readonly WP_LATEST=$(wget -q -O - "http://api.wordpress.org/core/version-check/1.5/" | head -n 4 | tail -n 1);

if [[ 'latest' = "$WP_VERSION" ]]; then
	WP_VERSION="$WP_LATEST"
	if [[ -z "$WP_LATEST" ]]; then
		printf "${RED}ERROR${RESET} Could not get latest WordPress version from api.wordpress.org. %s\n%s\n" "$CONNECTION" "$QUIT"
		exit_script
	fi
fi

# Set WordPress version.
readonly WP_VERSION="$WP_VERSION"

# Set default test suite version.
[[ -z "$WP_TS_VERSION" ]] && WP_TS_VERSION="$WP_VERSION"

# Install WordPress.
if [[ 'trunk' = "$WP_VERSION" ]]; then
	printf "Installing WordPress trunk... \n"
	svn export --quiet --force "https://develop.svn.wordpress.org/trunk/src/" "/tmp/tmp-wordpress/"
	rsync -a --delete "/tmp/tmp-wordpress/" "$WP_CORE_DIR"
elif [[ 'nightly' = "$WP_VERSION" ]]; then
	printf "Installing WordPress nightly... \n"
	if download "https://wordpress.org/nightly-builds/wordpress-latest.zip" "/tmp/wordpress-latest.zip"; then
		unzip -o -q "/tmp/wordpress-latest.zip" -d "/tmp/tmp-wordpress/"
		rsync -a --delete "/tmp/tmp-wordpress/wordpress/" "$WP_CORE_DIR"
	fi
else
	printf "Installing WordPress %s... \n" "$WP_VERSION"
	if download "https://wordpress.org/wordpress-$WP_VERSION.tar.gz" "/tmp/wordpress.tar.gz"; then
		tar --strip-components=1 -zxmf "/tmp/wordpress.tar.gz" -C "/tmp/tmp-wordpress/"
		rsync -a --delete "/tmp/tmp-wordpress/" "$WP_CORE_DIR"
	fi
fi

if [[ 'trunk' = "$WP_TS_VERSION" || 'nightly' = "$WP_TS_VERSION" ]]; then
	TS_ARCHIVE="trunk"
elif [[ $WP_TS_VERSION = 'latest' ]]; then
	TS_ARCHIVE="tags/$WP_LATEST"
	WP_TS_VERSION="$WP_LATEST"
else
	TS_ARCHIVE="tags/$WP_TS_VERSION"
fi

# Install WP test suite.
printf "Installing WordPress %s Test Suite...\n" "$WP_TS_VERSION"
if download_test_suite  "$TS_ARCHIVE" "$WP_TS_VERSION"; then
	rsync -a --delete "/tmp/tmp-wordpress-tests-lib/" "$WP_TESTS_DIR"
else
	if [[ 'trunk' = "$TS_ARCHIVE" ]]; then
		printf "%s\n" "$QUIT"
		exit_script
	fi

	printf "Installing Test Suite from trunk...\n"
	if download_test_suite "trunk" "trunk"; then
		rsync -a --delete "/tmp/tmp-wordpress-tests-lib/" "$WP_TESTS_DIR"
	else
		printf "%s\n" "$QUIT"
		exit_script;
	fi
fi

# Update credentials in the wp-tests-config.php file.
if [[ -f "$WP_TESTS_DIR/wp-tests-config.php" ]]; then
	printf "Updating wp-tests-config-sample.php...\n"

	sed -i "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" "$WP_TESTS_DIR/wp-tests-config.php"
	sed -i "s/youremptytestdbnamehere/wordpress_test/" "$WP_TESTS_DIR/wp-tests-config.php"
	sed -i "s/yourusernamehere/root/" "$WP_TESTS_DIR/wp-tests-config.php"
	sed -i "s/yourpasswordhere/root/" "$WP_TESTS_DIR/wp-tests-config.php"
	# Replacing local host with 127.0.0.1 to use TCP sockets instead of Unix domain sockets
	sed -i "s/localhost/127.0.0.1/" "$WP_TESTS_DIR/wp-tests-config.php"
fi

if [[ -f "$WP_TESTS_DIR/wp-tests-config.php" ]]; then
	# VVV has the tests config outside the $WP_TESTS_DIR dir.
	cp "$WP_TESTS_DIR/wp-tests-config.php" "/tmp/wp-tests-config.php"
fi

# Cleanup files.
clean_up_temp_files

echo "Chowning test folders to the circleci user"
chown -R circleci:circleci $WP_TESTS_DIR
chown -R circleci:circleci $WP_CORE_DIR

printf "\nFinished setting up packages\n\n"
