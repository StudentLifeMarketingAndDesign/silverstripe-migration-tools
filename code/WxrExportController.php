<?php

use SilverStripe\Blog\Model\BlogCategory;
use SilverStripe\Blog\Model\BlogTag;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\View\ArrayData;

class WxrExportController extends Controller {

	private static $url_handlers = [
		'wxrExport/$@' => 'index',
	];

	public function init() {
		parent::init();

		if (!Permission::check('ADMIN')) {
			//return $this->httpError(403);
			$response = $this ? $this->getResponse() : new HTTPResponse();
			$response->setStatusCode(403);
			return $this->redirect(Config::inst()->get('SilverStripe\\Security\\Security', 'login_url') . "?BackURL=" . urlencode($_SERVER['REQUEST_URI']));
		}
	}

	//In order to find all pages on the site
	// private static $pageFilters = [
	//     'ClassName:not' => 'Blog'
	//     'ClassName:not' => 'BlogPost',
	//     'ClassName:not' => 'TopicHolder',
	//     'ClassName:not' => 'Topic',
	// ];

	public function index($request) {

		$authors = new ArrayList();
		$pages = new ArrayList();
		$posts = new ArrayList();
		$tags = new ArrayList();
		$cats = new ArrayList();
		$tagsCats = array();

		$authors = Member::get();
		$pages = Page::get();

		$tags = BlogTag::get();
		$cats = BlogCategory::get();

		$tagsArray = $tags->toArray();
		$catsArray = $cats->toArray();

		$tagsCats = array_merge($tagsArray, $catsArray);

		//$blogTagsCats = $blogTags->merge($blogCats);

		$templateData = new ArrayData([
			'Authors' => $authors,
			'Pages' => $pages,
			'Tags' => $tags,
			'Categories' => $cats,
			'TagsCats' => $tagsCats,
		]);

		$renderedData = $templateData->renderWith('WxrFeed');

		$xml = simplexml_load_string($renderedData);

		$dom = new DOMDocument("1.0");
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml->asXML());
		echo $dom->saveXML();

	}
}
