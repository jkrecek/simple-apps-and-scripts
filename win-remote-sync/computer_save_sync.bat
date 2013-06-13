@ECHO off
set remote=\\REMOTE_PC_NAME\MOUNTPOINT
set local=C:\LOCAL_POINT
set route=ROUTE_TO_SAME_FOLDER
ECHO Connecting to secondary PC!
net use x: %remote%
ECHO Connection succesful, copying files!
xcopy "X:\%route%\*" "%local%\%route%\*" /Y /Q /S
ECHO Copying succesfully finished, unmounting secondary PC!
net use x: /delete /y
ECHO Done!
PAUSE
@ECHO on
