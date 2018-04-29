# fire-controller-web
web ui for the rearedinsteel (https://www.facebook.com/rearedinsteel/) flower tower fire controller

# Data Directories
The web root must have two read/write directories that are accessable by the webserver and the default user

These are:

uploads
playdir

Uploads is where the riff files and configuration file are stored.

playdir is where to copy them to be played.

# Websocket interface to relay controller

A websocket interface is avaialble on port 8080 on the controller.  The interace is simple. The following commands are supported.
  - set bitmask [time]
    - sets the bitmask, allows the next bitmask to be set after [time in ms optional] 
    - returns the newly set bitmask when compleat.
  - get
    - returns the current set state bitmask

#requirements
https://github.com/lowerpower/SainSmartUsbRelay

#also see
deno on of its operation on the flower tower
https://www.facebook.com/rearedinsteel/videos/713271008877496/

## Links
* https://github.com/lowerpower/SainSmartUsbRelay - relay driver/websocket backend for this project.
* https://www.facebook.com/rearedinsteel/ - Reared In Steel facebook.  
* https://www.facebook.com/rearedinsteel/videos/713271008877496/ - project in action on flower tower
* [Video Demo](https://www.youtube.com/watch?v=d_1EEWdWekI) of this project integrated into a solution.
* [websocketd](https://github.com/joewalnes/websocketd) - allows websocket control of a stdio program.

