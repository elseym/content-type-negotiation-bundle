## Symfony 2 Content Type Negotiation Bundle

This bundle can be used to enable a simple content type negotiation in Symfony2 applications. To use it, just include it as a dependency in your composer.json and register the bundle in your AppKernel:

`composer.json`:

	...
	"require": {
		...
		"elseym/content-type-negotiation-bundle": "dev-master"
	}
	...

`app/AppKernel.php`:

	...
	$bundles = array(
		...
		new elseym\ContentTypeNegotiationBundle\elseymContentTypeNegotiationBundle(),
		...
	);

### Usage

The key component of this bundle is an `EventListener` that registeres for `kernel.controller` events and selects the best suitable controller action for each request. This decision is based on the value of a requests `Accept` header.

For a request with an `Accept` header like this:

	Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8

The bundle would try to use one the following controller actions:

- indexActionHtmlText
- indexActionHtml
- indexActionXhtmlXmlApplication
- indexActionXhtmlXml
- indexActionXmlApplication
- indexActionXml
- indexAction
