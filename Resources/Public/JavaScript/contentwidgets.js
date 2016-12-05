/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Prepares the loading of content provided by TYPO3 on any page.
 *
 * Examples:
 * ---------
 *
 * cwidgets('www.mysite.com','lib','my_lib_name');
 * cwidgets('www.mysite.com','lib','my_lib_name',2235,'my_target_element','My_Function_To_Call');
 *
 * @param url Url of the page providing the content code (with slash at the end and no protocol instruction)
 * @param ctype Content type (can be 'records' or 'lib')
 * @param cvar Content id (can be the uid of the tt_content loaded or the name of the library)
 * @param rtype Render type (by default '4653')
 * @param target Id of the DOM element that will be replaced by the loaded content code
 * @param callback JS function to call once the code is loaded
 */
function cwidgets(url, ctype, cvar, rtype, target, callback) {

    // Exit early if URL is undefined
    if (typeof url === 'undefined' && url == null) {
        return '';
	}
    // render type id
    if (typeof rtype === 'undefined' || rtype == null) {
    	rtype = '4653';
	}

	var targetId;
    if (typeof target === 'undefined' || target == null) {
        targetId = 'cwidgets_' + Math.floor(Math.random() * 99999999999);
        // Outputting the container
        document.write('<div id="' + targetId + '" class="loading">loading ...</div>');
    } else {
        targetId = target;
    }

    var cBack;
    // Callback after container is updated
    if (typeof callback != 'undefined' && callback != null) {
        cBack = '&cw_cback=' + callback;
    }
    else {
        cBack = '';
    }

    var urlParts = parse_url(url);

    // Building the script URL
    var scriptUrl = '/';
    if (urlParts.path != undefined) scriptUrl = urlParts.path;
    if (urlParts.scheme != undefined) scriptUrl = urlParts.scheme + '://' + urlParts.host + urlParts.path;

	var moreQuery;
    if (urlParts.query != undefined) {
    	moreQuery = '&' + urlParts.query;
	}
    else {
    	moreQuery = '';
	}
    scriptUrl = scriptUrl + '?type=' + rtype // Rendering Page type
                        + '&cw_ctype=' + ctype // Content type (lib or record)
                        + '&cw_cvar=' + cvar // Content name or Id
                        + '&cw_cuid=' + targetId // Target element id
                        + moreQuery // Additional query variables of the current page
                        + cBack; // Optional callback

    jQuery(document).ready(function() {
        jQuery.ajax(
                {
                    url: scriptUrl,
                    success: function(data) {
                    	var targetElement = jQuery('#' + targetId);
						targetElement.after(data);
						targetElement.remove();
                    },
                    statusCode: {
                        404: function() {
                            console.log('page not found');
                        }
                    }
                }
        );
    });

}


/**
 * Parses a url, as the PHP function does
 * @param str Url to parse
 * @return string the url parsed
 */
function parse_url(str) {

    var key = ['source', 'scheme', 'authority', 'userInfo', 'user', 'pass', 'host', 'port',
               'relative', 'path', 'directory', 'file', 'query', 'fragment'],
            ini = (this.php_js && this.php_js.ini) || {},
            mode = (ini['phpjs.parse_url.mode'] &&
                    ini['phpjs.parse_url.mode'].local_value) || 'php',
            parser = {
                php: /^(?:([^:\/?#]+):)?(?:\/\/()(?:(?:()(?:([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?()(?:(()(?:(?:[^?#\/]*\/)*)()(?:[^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
                strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
                loose: /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/\/?)?((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/ // Added one optional slash to post-scheme to catch file:/// (should restrict this)
            };

    var m = parser[mode].exec(str),
            uri = {},
            i = 14;
    while (i--) {
        if (m[i]) {
            uri[key[i]] = m[i];
        }
    }

    if (mode !== 'php') {
        var name = (ini['phpjs.parse_url.queryKey'] &&
                    ini['phpjs.parse_url.queryKey'].local_value) || 'queryKey';
        parser = /(?:^|&)([^&=]*)=?([^&]*)/g;
        uri[name] = {};
        uri[key[12]].replace(parser, function ($0, $1, $2) {
            if ($1) {
                uri[name][$1] = $2;
            }
        });
    }
    delete uri.source;
    return uri;
}


/**
 * Adds a function call to the "onload" event of the current page
 * only if no JS framework is loaded
 * @param func
 * @return void
 */
function addLoadEvent(func) {
    var oldonload = window.onload;
    if (typeof window.onload != 'function') {
        window.onload = func;
    } else {
        window.onload = function() {
            if (oldonload) {
                oldonload();
            }
            func();
        }
    }
}
