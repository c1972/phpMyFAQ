<?php

/**
 * FAQ overview page.
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at https://mozilla.org/MPL/2.0/.
 *
 * @package   phpMyFAQ
 * @author    Thorsten Rinne <thorsten@phpmyfaq.de>
 * @copyright 2015-2024 phpMyFAQ Team
 * @license   https://www.mozilla.org/MPL/2.0/ Mozilla Public License Version 2.0
 * @link      https://www.phpmyfaq.de
 * @since     2015-09-27
 */

use phpMyFAQ\Helper\FaqHelper;
use phpMyFAQ\Template\CategoryNameTwigExtension;
use phpMyFAQ\Template\TwigWrapper;
use phpMyFAQ\Translation;
use Twig\Extension\DebugExtension;

if (!defined('IS_VALID_PHPMYFAQ')) {
    http_response_code(400);
    exit();
}

$faqSession->userTracking('overview', 0);

$faqHelper = new FaqHelper($faqConfig);

$faq->setUser($currentUser);
$faq->setGroups($currentGroups);

$twig = new TwigWrapper(PMF_ROOT_DIR . '/assets/templates');
$twig->addExtension(new DebugExtension());
$twig->addExtension(new CategoryNameTwigExtension());
$twigTemplate = $twig->loadTemplate('./overview.twig');

$templateVars = [
    'pageHeader' => Translation::get('faqOverview'),
    'faqOverview' => $faqHelper->createOverview($category, $faq, $faqLangCode),
    'msgAuthor' => Translation::get('msgAuthor'),
    'msgLastUpdateArticle' => Translation::get('msgLastUpdateArticle')
];

$template->addRenderedTwigOutput(
    'mainPageContent',
    $twigTemplate->render($templateVars)
);
