A little php script to automatically build a release archive whenever you push to your branch

[![Issue Count](https://codeclimate.com/github/Idrinth/github-auto-release/badges/issue_count.svg)](https://codeclimate.com/github/Idrinth/github-auto-release)
[![Code Climate](https://codeclimate.com/github/Idrinth/github-auto-release/badges/gpa.svg)](https://codeclimate.com/github/Idrinth/github-auto-release)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/0bafe99532f1414dab5be0ca7a621e55)](https://www.codacy.com/app/Idrinth/github-auto-release?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Idrinth/github-auto-release&amp;utm_campaign=Badge_Grade)

Setup
-----

- Open up the config.ini and enter the github-specific data(username,password).
- Create a repository (/owner/name) as a group and add the avaible keys as seen in the example
- deploy to a server
- setup github to submit push requests to this script(url is the url, secret is the key you entered)