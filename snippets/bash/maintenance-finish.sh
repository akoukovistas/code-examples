#!/bin/bash
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
					m-finish-hotfix.sh
					#if production exists push to it
					if git remote | grep production > /dev/null; then
						#make sure we're on master
						git checkout master
						git push production master
					fi
			fi
	fi
done
