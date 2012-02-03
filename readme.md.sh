A little php script to automatically build a release archive whenever you push to your branch

Setup
=====

__Configure the script__
- Open up the release.php and set up the config. enter your username, password, source, repo, whatever you want for your key, and what you want your release file to be called.

__Set up service hooks__
- Go to your project page on http://github.com
- Click on Admin and click on Service Hooks
- Enter the URL you put the script at (ex: http://yourdomain.com/release.php?key=yourkeyhere)
