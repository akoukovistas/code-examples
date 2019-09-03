#script to handle deletions and updates during migration
#let's delete some plugins
if wp plugin is-installed force-regenerate-thumbnails
	then
	wp plugin delete force-regenerate-thumbnails
	git commit -a -m "Delete force-regenerate-thumbnails"
fi
if wp plugin is-installed  gathercontent-import
	then
	wp plugin delete  gathercontent-import
	git commit -a -m "Delete  gathercontent-import"
fi
if wp plugin is-installed  simply-show-hooks
	then
	wp plugin delete  simply-show-hooks
	git commit -a -m "Delete  simply-show-hooks"
fi
if wp plugin is-installed  enable-media-replace
	then
	wp plugin delete  enable-media-replace
	git commit -a -m "Delete  enable-media-replace"
fi
if wp plugin is-installed  regenerate-thumbnails
	then
	wp plugin delete  regenerate-thumbnails
	git commit -a -m "Delete  regenerate-thumbnails"
fi
if wp plugin is-installed  velvet-blues-update-urls
	then
	wp plugin delete  velvet-blues-update-urls
	git commit -a -m "Delete  velvet-blues-update-urls"
fi
if wp plugin is-installed  wp-site-migrate
	then
	wp plugin delete  wp-site-migrate
	git commit -a -m "Delete  wp-site-migrate"
fi

#remove all inactive plugins
wp plugin delete $(wp plugin list --status=inactive --field=name)
git commit -a -m "delete all inactive plugins"

#let's install some stuff

#check if 2017 is installed, and install if it isn't
if ! wp theme is-installed twentyseventeen
	then
	wp theme install twentyseventeen
	git add -A
	git commit -a -m "add twentyseventeen"
fi

#let's get autoptimize in here
if ! wp plugin is-installed autoptimize
	then
	wp plugin install autoptimize --activate
	git add -A
	git commit -a -m "add autoptimize"
fi

#let's update the thing
wp core update --quiet
d=$(wp core version)
git add -A
git commit -a -q -m "Update WordPress to $d"
#check if acf pro is a thing and if so add the key
d=$(wp plugin get --field=status advanced-custom-fields-pro)
if [ "$d" = "active" ]
	then
	wp eval 'acf_pro_update_license("LICENCEKEY");'
fi
#update all plugins and commit
file=".plugin-exceptions"
#if plugin exceptions exist, keep them in mind
if [ -f "$file" ]
	then
	except=`cat .plugin-exceptions`
	wp plugin update --all --quiet --exclude="$except"&> .plugin-updates
else
	wp plugin update --all --quiet &> .plugin-updates
fi
d=$(<.plugin-updates)
git add -A
git commit -a -q -m "Update Plugins" -m "$d"
