#!/bin/bash

##set our options
test='active'
#the themes to be installed
#theme="https://github.com/bigspring/M3/archive/master.zip"
theme=('https://github.com/olefredrik/FoundationPress/archive/master.zip')

#the standard plugins
plugins=('advanced-text-widget' 'amp' 'breadcrumb-navxt' 'cms-tree-page-view' 'custom-post-type-ui' 
	'force-regenerate-thumbnails' 'imsanity' 'widget-logic' 'wordpress-seo' 'classic-editor')

#install WP 
if [ $test = 'active' ]
then
	# ##get the details for the install
	echo Project Name:
	read projectName
	#make sure no pillock puts bad characters
	while  [[ "$projectName" =~ [^-0-9a-zA-Z]+  ]]; do
	  echo "Invalid input, Project name must be only letters, numbers or dashes"
	  read projectName
	done
	echo Database Host:
	read databaseHost
	echo Database Name:
	read databaseName
	echo Database Username:
	read databaseUser
	echo Database Password:
	read databasePassword
	echo Database Prefix \(with underscore\):
	read databasePrefix
	echo WP Username:
	read wpUsername
	echo WP Password:
	read wpPassword
	echo WP Email:
	read wpEmail
	#make sure it's an email
	while  [[ ! "$wpEmail" =~ ^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$  ]]; do
	  echo "Invalid email, WordPress Email must be a valid email"
	  read wpEmail
	done
	echo "WP Language (usually en_GB):"
	read wpLanguage
	

	#make the dir and download wp
	mkdir $projectName && cd $projectName
	wp core download --locale=$wpLanguage

	#make the config
	wp config create --dbname=$databaseName --dbuser=$databaseUser --dbpass=$databasePassword --dbhost=$databaseHost --dbprefix=$databasePrefix
	wp core install --url=http://localhost/$projectName --title=$projectName --admin_user=$wpUsername --admin_password=$wpPassword --admin_email=$wpEmail

	#remove the blog description
	wp option update blogdescription ""
fi

if [ $test = 'active' ]
then
	for i in "${plugins[@]}"
	do
		:
		wp plugin install $i
	done

	#install ACF Pro - this surprisingly worked
	wp plugin install 'https://connect.advancedcustomfields.com/index.php?a=download&p=pro&k=LICENSEKEY'
	#install Gravity Forms - this key resets
	wp plugin install 'http://s3.amazonaws.com/gravityforms/releases/gravityforms_2.3.4.3.zip?LICENSEKEY'

fi


if [ $test = 'active' ]
then
	#needs to handle theme better - rename, remove git
	wp theme install --activate $theme
fi