# Legal Case Management Software for Non-Profits

Legal case management (LCM) is a software aimed for use by not-for-profit legal
advice centres in order to make better follow-ups of their work, including
client consultations and court events, as well as more efficient reporting (see
also: software overview). LCM is distributed as free software under the terms
of the GNU General Public License (GPL), either version 2 of the License, or
(at your option) any later version. See the LICENSE.md file for more information.

LCM is minimally maintained. The project was created in 2004 and active development
stopped around 2008 (see History below). However, since there are quite a few
organisations still using it, we are happy to provide minimal bugfix support for
newer PHP versions. New features are very unlikely, however.

PHP8 support was added thanks to Sestino Barone (SestinoBarone.com).

## Requirements

* PHP: The latest release is tested on PHP 8.0 and should work with PHP 7.0 and later.
* MySQL: The latest release is tested on MariaDB 10.3, and should work with MySQL 5.0 and later. The Postgres integration has not been tested recently.

## Installation

Download the latest tar.gz archive from our Source Forge project page
<http://sf.net/projects/legalcase> and uncompress it in a temporary
directory:

    $ cd /tmp/
    $ tar xzf /path/to/legalcase-X.Y.Z.tar.gz

After decompressing the archive, there will be a new directory called legalcase:

    $ ls -l

    drwxr-xr-x 6 user user 4096 2005-03-09 10:26 legalcase

Move the legalcase directory in a space accessible by the Apache Web server:

    $ mv legalcase /var/www/

Provide the permission to "write" to the Apache Web server for the directories
log, inc/config and inc/data:

    $ cd /var/www/legalcase/
    $ chmod a+rw log inc/config inc/data

Access to this directory from your Web browser: <http://localhost/legalcase/> 
and follow the instructions from the installation assistant.

LCM relies on the timezone set by PHP:  
https://www.php.net/manual/en/datetime.configuration.php#ini.date.timezone

If you cannot change the setting, we recommend adding this at the end of the `inc/config/inc_connect.php` file:

```
date_default_timezone_set('America/New_York');
```

You can find a full list of supported timezones here:  
https://www.php.net/manual/en/timezones.php

## History

The Legal Case Management project was initiated by the Internet Rights Bulgaria
Foundation (IRBF, www.irbf.ngo-bg.org) in July 2004, as a result of research
and collaboration with various Bulgarian legal advice centres and the Cambridge
Independent Advice Centre (CIAC, www.ciac.org.uk) in 2003.

The project was initially made possible with the financial support of the Information
Program of the Open Society Institute (OSI, www.soros.org), and with the
collaboration of the Law Program of the Bulgarian Open Society Institute
(OSI-bg, www.osi.bg).

In 2021, PHP8 support was added thanks to Sestino Barone (SestinoBarone.com).

The main contributors to the project are:

- Christina Haralanova (Project manager)
- Mathieu Lutfy (Software development leader)
- Anastas Giokov (PHP/MySQL development)
- Krasimir Makaveev (Web development and quality assurance)
- Kaloian Doganov (Documentation and quality assurance)
- Alexander Shopov (Documentation)

Translators:

- Alexander Shopov, Bulgarian
- Maria Velichkova, Bulgarian
- Mathieu Lutfy, French
- Kilian Huber, German
- André Grötschel, German
- Rodolfo Cappa, Italian
- Mateusz Hołysz, Polish
- Luís André Beckhauser, Portuguese of Brasil
- Sergey Salnikov, Russian
- Roberto Leal Guerra, Spanish

Quality assurance:

-  Bulgarian Open Society Institute, www.osi.bg (legal consultation, structural testing)
-  Bulgarian Helsinki Committee, www.bghelsinki.org (legal consultation, behavioral and structural testing)
-  Inter-Space, www.i-space.org (user-interface quality assurance, promotion and distribution)
-  Chris Bailey -- CIAC, www.ciac.org.uk (behavioral testing, English proof-reading)

Web hosting:

- Source Forge, www.sourceforge.net (download, CVS, mailing-lists)
- NGO Bulgaria Gateway, www.ngo-bg.org (website, documentation, testing sites)

Financial support provided by:

- Open Society Institute (2004-2005)
- Cambridge Independent Advice Centre, FOSS programme (2006)

Hosting:

- CVS hosting by Source Forge (2004-2006) https://www.sourceforge.net/projects/legalcase/
- Web site hosting provided by: NGO Bulgaria Gateway (2004-2006) http://www.ngo-bg.org

Project coordination work and reporting done by: Christina Haralanova (2004-2005) Internet Rights Bulgaria Foundation http://www.socialrights.org/irbf

We would also like to thank the authors of the Spip (https://www.spip.net) content
management system, from which many concepts were re-used. As well as the
Trad-Lang (https://trad.spip.net/) translation system by Florent
Jugla, which is very helpful to manage the LCM translations.
