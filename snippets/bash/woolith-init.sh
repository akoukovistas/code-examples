#!/bin/bash
test='active'

CURRENT=`pwd`
if [ $test = 'active-install' ]
then
#update woolith
cd ~/woolith
git checkout master
git pull origin master


#go back to wp install
cd "$CURRENT"
#set our options
wp plugin install 'woocommerce'
fi

if [ $test = 'active' ]
then
	#read the theme name
	echo Theme Directory Name:
	read themeName
fi

if [ $test = 'active' ]
then
	#copy the library folder to the relevant location
	#TODO: make the theme name dynamic
	cp -R ~/woolith/library/woocommerce "$CURRENT"/wp-content/themes/"$themeName"/library/woocommerce

	#copy the scss assets
	cp -R ~/woolith/src/assets/scss/woocommerce "$CURRENT"/wp-content/themes/"$themeName"/src/assets/scss/woocommerce

	#template parts
	cp -R ~/woolith/template-parts/woocommerce "$CURRENT"/wp-content/themes/"$themeName"/template-parts/woocommerce

	#woocommerce overrides
	cp -R ~/woolith/woocommerce "$CURRENT"/wp-content/themes/"$themeName"/woocommerce
fi

if [ $test = 'active' ]
then
	echo " ">>"$CURRENT"/wp-content/themes/"$themeName"/functions.php
	echo "/** Woocommerce scripts */">>"$CURRENT"/wp-content/themes/"$themeName"/functions.php
	echo "require_once( 'library/woocommerce/functions.php' );">>"$CURRENT"/wp-content/themes/"$themeName"/functions.php
fi

if [ $test = 'active' ]
then
	echo " ">>"$CURRENT"/wp-content/themes/"$themeName"/src/assets/scss/app.scss
	echo "// Woocommerce.">>"$CURRENT"/wp-content/themes/"$themeName"/src/assets/scss/app.scss
	echo "@import \"woocommerce/components/card\";">>"$CURRENT"/wp-content/themes/"$themeName"/src/assets/scss/app.scss
	echo "@import \"woocommerce/components/filtering\";">>"$CURRENT"/wp-content/themes/"$themeName"/src/assets/scss/app.scss
	echo "@import \"woocommerce/components/lists\";">>"$CURRENT"/wp-content/themes/"$themeName"/src/assets/scss/app.scss
	echo "@import \"woocommerce/components/messages\";">>"$CURRENT"/wp-content/themes/"$themeName"/src/assets/scss/app.scss
	echo "@import \"woocommerce/components/pagination\";">>"$CURRENT"/wp-content/themes/"$themeName"/src/assets/scss/app.scss
	echo "@import \"woocommerce/components/product-loop\";">>"$CURRENT"/wp-content/themes/"$themeName"/src/assets/scss/app.scss
	echo "@import \"woocommerce/components/sale-bubble\";">>"$CURRENT"/wp-content/themes/"$themeName"/src/assets/scss/app.scss
	echo "@import \"woocommerce/components/tables/cart-table\";">>"$CURRENT"/wp-content/themes/"$themeName"/src/assets/scss/app.scss
	echo "@import \"woocommerce/components/tables/cart-totals\";">>"$CURRENT"/wp-content/themes/"$themeName"/src/assets/scss/app.scss
	echo "@import \"woocommerce/components/tables/variations-table\";">>"$CURRENT"/wp-content/themes/"$themeName"/src/assets/scss/app.scss
	echo "@import \"woocommerce/templates/checkout\";">>"$CURRENT"/wp-content/themes/"$themeName"/src/assets/scss/app.scss
	echo "@import \"woocommerce/templates/single-product\";">>"$CURRENT"/wp-content/themes/"$themeName"/src/assets/scss/app.scss
fi