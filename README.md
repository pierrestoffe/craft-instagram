# Instagram plugin for Craft CMS 3.x

Instagram integration for Craft CMS

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require pierrestoffe/craft-instagram

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Instagram.

## Using Instagram

This plugin includes 5 template variables:

* `craft.instagram.getMediaFromUser(instagramUser)` in order to get the 25 latest media from the authenticated Instagram user

* `craft.instagram.getMediaFromUrls(instagramUrlsArray)` in order to get the media information of specific Instagram media

* `craft.instagram.getSavedInstagramAccessToken(instagramUser)` in order to get the access token used to fetch data with the Instagram API

* `craft.instagram.getSavedInstagramAccessTokens` in order to get all the access tokens used to fetch data with the Instagram API

* `craft.instagram.getSavedFacebookAccessTokens` in order to get all the access tokens used to fetch data with the Facebook API

## Instagram Roadmap

Some things to do, and ideas for potential features:

* Generate the first long-lived access token from the App ID and App Secret.

Brought to you by [Pierre Stoffe](https://pierrestoffe.be)
