Shared resource module
================================

This module is the result of a five year long run work with Intel, Catalyst and
French Ministry of Education. 

Initially bound to provide Moodle 1.9, pre-repository architectures with a centralized
repository. This version has been developped for ensuring content continuity of all 
implementations using Intel's TAO based repositoy approach. 

Further this original goal, there was beside an opportunity to provide Moodle 2 with
a yet internalized files repository approach as an alternative to use external document
systems. Sharedresource based repo and resource management provides the first Moodle
internally handled rich indexing resource database using full Dublin Core metadata 
base infrastructure, and allowing any Dublin core based schema to be added and used as
master indexing schema. 

Shared resource module is the key part of a full indexed public resource center that 
will come as 4 complementary parts : 

- Shared resource module (this package) : master part 
- Shared resources block : Utilities to access to central library and make some resource conversions or feeding
- Shared resource repository : A Repository view of the shared resource storage area, so shared resource can also be used and picked
as standard resource instances, or in other publication contexts
- Shared Resource Local Component : provides a front-end to librarian to search, browse and get some site level services around shared resources.

Shared resources in the actual development state provides : 

- An activitty module (this module) allowing making new resources and share them with indexing information
into the reosurce catalog. 
- Full handling of LOM, LOMFR, ScoLOMFR, SupLOMFR (french variants and enrichements to LOM)
- Extensible metadata plugin architecture allows easily adding more support to other Dublin Core based formats
- Metadata user profile definition, allowing to reduce the metadata impact on user experience to the "just necessary"
- Configurable search engine, allowing choosing which attributes to use for searching
- Localisation of the sharing level to a course category, making category scoped private repositories.
- OAI-PMH exposition on sharedresource entries (site level)
- External resource submission gate for remote feeding the library from external authoring tools
- MNET architecture of services to publish and search remotely in a "provider/consumer" definition

Installation
==============

You will need to install all parts to get proper behaviour of the sharedresource system. All parts 
must be unzipped into the proper plugin locations, then site notification will be run for installing
all elements. 

When default settings are required, you will be asked to choose for a metadata schema to use for all resources,
and a metadata configuration grid will be presented to define each of three user profiles related to metadata : 

- Administrator profile : the widest access to metadata in the resource information form
- Librarian profile : Usually has access to indexing and identification fields
- Author profile : Usually restricted to "essential information" authors are supposed to be able to provide, pursuant their 
background of indexation technology, or the access they may have or be required to be able to regarding qualified information.   