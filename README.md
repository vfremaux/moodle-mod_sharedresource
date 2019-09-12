Sharedresource module
=====================

This module initially intended for Moodle 1.9 implementation provides a site-wide sharing
repository engine including full metadata support. 

Credits : Highly inspired from a co-work with Catalyst Ltd (Piers Harding) for Intel Teach Advanced Online programs.

Versions
========

Moodle 2.7 to Moodle 3.4

Dual distribution of a community version and a "pro" version wich adds industry level features such as 
library networking, webservices or mass resource import.

Handling full standards oriented to French variants. Other variants easy to implement:

- Dublin core
- LOM
- LOMFR
- ScoLOMFR
- SupLOMFR

Related Components
==================

Shared resource works together with a central addditional component of Moodle that will provide "central"
management entry point for the shared resource catalog. Please check
http://github.com/vfremaux/moodle-resources repository for information.

Changes for 2018021700
=======================

Fix subplugin names in settings

Changes for 2018110200 (x.x.0009)
=================================

Large rework of the remote library bindings. Add security to the remote library access when 
remote library is a private catalog (uses auth_ticket). Add scorm local and remote detection
and quick deployment mode.

Changes for 2019062000 (x.x.0010)
=================================

Add capabilities to control classification and classification tokens deletion.