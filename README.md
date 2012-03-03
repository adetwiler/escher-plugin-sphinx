# Escher Plugin - Sphinx #

Sphinx is an Open Source Search Server that allows you to connect with MySQL
to provide different search methods, such as full-text searches.
Sphinx is required in order for this plugin to work.

[Download Sphinx][1]

## Getting Started ##

1. Install Sphinx.
2. Configure your sources and indexes in sphinx.conf.
3. Build your indexes.
4. Place something similar in your [Escher][2] config:

	```
	$datasource['sphinx'] = array(
		'type' => array('sphinx','default'),
		'settings' => array(
			'prefix' => 'idx_',
			'host' => 'localhost',
			'port' => 3312,
		)
	);
	```

5. Begin searching in just two lines of code:

	```
	$sphinx = Load::Datasource('sphinx');
	$results = $sphinx->get('blog',array('body'=>'This is my search text'));
	```

Refer to [Sphinx documentation][3] to get more information on how to setup your sources and indexes.

Escher Plugin - Sphinx was developed by [Andrew Detwiler][4].

Want to get involved?  Submit issues or fork Escher Plugin - Sphinx on [GitHub][5].

## License ##

Escher Plugin - Sphinx is dual-licensed under MIT and GPL. Please see the [MIT License][6] and
[GNU General Public License][7] for details.

[1]: http://sphinxsearch.com/downloads/
[2]: http://github.com/thomshouse/escher
[3]: http://sphinxsearch.com/docs/
[4]: https://plus.google.com/115067270129450960275/about
[5]: https://github.com/adetwiler/escher-plugin-sphinx
[6]: http://en.wikipedia.org/wiki/MIT_License
[7]: http://www.gnu.org/licenses/gpl.html