2018-06-27, Frans Lundberg


1806-project-description.md
===========================

Initial project description, notes, ideas, thoughts.


Priorities
----------

1. Binson serialization (do first).
2. Binson parsing.


Wanted
------

Code that can easily be run server-side in a typical web hosting environment; for example 
a LAMP setup with WordPress. Should work for typical low-cost hosting providers.
For example, siteGround.com / "Web Hosting".

The code in this repo should work in a WordPress plugin.

Of course, the code should have automatic unit tests.
Code conventions: use whatever that is common in the PHP community.


Consider
--------

* Can existing C code be reused? PHP module of some kind?
* What requirements come from using it in a WordPress plugin? For example, can we
  use C code PHP modules in a WordPress plugin?
  
