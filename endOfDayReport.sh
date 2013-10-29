#!/bin/bash
#get the current script folder
CURRENTDIR=$(dirname $0)
#enter the current script folder
cd $CURRENTDIR

app/console Get:DayStatisticsCommand