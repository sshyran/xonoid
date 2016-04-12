# <font color='#ff0000'>Project development is halted.</font>_#_

XoNoiD CRM is helpdesk ticket system, customer list, customer network assets list.
It's based on Zend Framework.

# Basic idea #
  * Create new company
  * Add contact user
  * Add branch offices
  * Add network devices
    * Add network device ports
    * Add VLANs and IP addresses
  * Add domains

## Customer can ##
  * Create tickets for your helpdesk
  * Control all of his network assets

# Requirements #
  * GNU/Linux (may work with MS Windows XP or better, not tested)
  * Apache 2
    * mod\_rewrite
    * .htaccess
    * SSL (HTTPS) certificate for Apache (optional, recommended for encrypted connection)
  * PHP 5
    * PHP Modules:
      * Reflection
      * PHP Gettext
      * PDO
      * PDO\_MySQL
      * XDebug for debugging (optional)
    * Libraries / Classes
      * Zend Framework 1.7.0+ (included)
        * Dojo, Dojox, Dijit (included)
      * [TCPDF](http://www.tecnick.com/public/code/cp_dpage.php?aiocp_dp=tcpdf) (included)
      * [ZF DataGrid](http://code.google.com/p/zend-framework-datagrid/) (included)
  * MySQL 5.0+

Icons from
  * http://commons.wikimedia.org/wiki/Crystal_Clear
  * http://www.everaldo.com/crystal/

Developement tools/software used
  * PSPad
  * VMWare Server
  * MySQL Workbench
  * PHPMyAdmin
  * Mozilla Firefox
  * TortoiseSVN
  * poEdit
  * GNU Gettext
  * Paint.NET

See [Setup](Setup.md) page for setting up.