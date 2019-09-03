#!/bin/bash
#glorious blocker
file=".potato"
#twentydays in seconds
twentydays='1728000'
twodays='172800'
HIGHLIGHT='\033[0;35m'
NC='\033[0m' # No Color
#needs to be portable tbh
for dir in /home/alexander/Desktop/htdocs/*
do
	test -d "$dir" || continue
	#it takes the letter you enter with the command
	if [[ ${dir:31:1} == $1 ]]
		then
			cd $dir
			if ! [ -f "$file" ]
				then
					echo -e "$HIGHLIGHT $dir $NC"
					m-git-pre.sh
					wp core update --quiet
					d=$(wp core version)
					git add -A
					git commit -a -q -m "Update WordPress to $d"
					file2=".plugin-exceptions"
					#if plugin exceptions exist, keep them in mind
					if [ -f "$file2" ]
						then
						except=`cat .plugin-exceptions`
						wp plugin update --all --quiet --exclude="$except"&> .plugin-updates
					else
						wp plugin update --all --quiet &> .plugin-updates
					fi
					d=$(<.plugin-updates)
					git add -A
					git commit -a -q -m "Update Plugins" -m "$d"
					wp plugin install classic-editor --activate
					git add -A
					git commit -a -m "add classic-editor"
			fi
	fi
done
