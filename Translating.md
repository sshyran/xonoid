#How to translate

# How to translate #
  * Get latest HEAD version from SVN trunk
  * Go to **locales** directory
## New translation ##
  * Download poEdit from http://www.poedit.net/
  * copy **locales/xonoid.pot** to **languagecode.po**
    * Example: **fi\_FI.po**
  * Open .po file in poEdit
  * Set catalog settings to your language
    * Use UTF-8 as character encoding
  * Translate
  * Send .po file to me and I'll add it to SVN

## Existing translation ##
  * Make sure you have installed
    * Python 2.5 or newer
    * gettext tools
  * Run `python update.py`
  * open **languagecode.po** in poEdit
    * Example: **fi\_FI.po**
  * Translate
  * Create .patch/.diff file from .po file with your favorite SVN client
  * Send .patch/.diff file to me and I will add it to SVN