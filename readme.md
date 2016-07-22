A little php script to automatically build a release archive whenever you push to your branch

[![Issue Count](https://codeclimate.com/github/Idrinth/github-auto-release/badges/issue_count.svg)](https://codeclimate.com/github/Idrinth/github-auto-release)
[![Code Climate](https://codeclimate.com/github/Idrinth/github-auto-release/badges/gpa.svg)](https://codeclimate.com/github/Idrinth/github-auto-release)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/0bafe99532f1414dab5be0ca7a621e55)](https://www.codacy.com/app/Idrinth/github-auto-release?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Idrinth/github-auto-release&amp;utm_campaign=Badge_Grade)

Setup
-----

__Configure the script__

- Open up the release.php and set up the config. enter your username, password, source, repo, whatever you want for your key, and what you want your release file to be called.

__Set up service hooks__

- Go to your project page on http://github.com
- Click on Admin and click on Service Hooks
- Enter the URL you put the script at (ex: http://yourdomain.com/release.php?key=yourkeyhere)
