# Monitor

Monitor is a component for [Joomla!](https://www.joomla.org). It has all you need to create issue trackers for all your projects and integrate them into your Joomla! site.

## Requirements

* Joomla! 3.x
  
## Installation

* [Download](https://github.com/Harmageddon/com_monitor/releases) the latest release.
* Install it on your web site.
* At *Components - Monitor - Projects*, create a new project.
* At *Components - Monitor - Classifications*, create one or more classifications.
* At *Components - Monitor - Status*, create as many status values, as you want.
* Create some menu items, if you're in the mood.
* That's it! You're ready to go!

## Features

### Key features

* Create one or multiple **projects**.
* **Issues**:
  * Addressed to one specific *project*.
  * *Classification* decides who is permitted to view the issue.
  * *Status* is set by commenting.
* **Classifications**: Can be created for each project. Every classification can require a certain access level to view issues.
* **Status**: Different status values are defined for each project.
  * Every status can be *open* or *closed*.
  * Per project, there is one *default* status.
* **Comments**:
  * Conversation below each issue.
  * The *status* of the issue can be changed with every comment.
  * Every status change can be displayed along with the comment.
* Monitor supports displaying user avatars with [CMAvatar](https://github.com/cmextension/cmavatar)
  
### Views

* All projects
* Project page: Displays logo, description and URL for one project.
* Issues: List of issues, filtered by project.
  Additional filters: Title, Status, Classification, Author
* Issue: Displays information on a single issue (Text, Author, Status, Classification, Version,...) and all comments for the issue.
* Issue form
* Comment form

### Even more features (plugins)

* [Contributions](https://github.com/Harmageddon/plg_monitor_contributions): Shows the issues and comments by every user on their contact page.
* [Quote](https://github.com/Harmageddon/plg_monitor_quote): Provides functionality to insert quotes in comments.

Do you miss anything? Are you encountering any problems with this extension? Send me a mail or create an issue!
