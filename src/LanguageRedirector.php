<?php
namespace liquidbcn\languageredirect;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use liquidbcn\languageredirect\models\Settings;
use yii\base\Event;

class LanguageRedirector extends Plugin
{
    public bool $hasCpSettings = true;

    public function init()
    {
        parent::init();

        // Register an event handler for URL management
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $this->redirect();
            }
        );
    }

    protected function redirect()
    {
        $browser = Craft::$app->getRequest()->getHeaders()->get('accept-language');
        $default = $this->getSettings()->defaultLanguage;
        $urls = $this->getSettings()->urls;

        if (!empty($urls)) {
            $browserLoc = new \koenster\PHPLanguageDetection\BrowserLocalization();
            $browserLoc->setAvailable(array_keys($urls))
                ->setDefault($default)
                ->setPreferences($browser);

            $url = parse_url(Craft::$app->getRequest()->getUrl());

            if (Craft::$app->getRequest()->getIsGet()) {
                if (empty($url['path']) || $url['path'] === '/') {
                    $language = $browserLoc->detect();
                    if (isset($urls[$language])) {
                        Craft::$app->getResponse()->redirect($urls[$language], 301)->send();
                        Craft::$app->end();
                    }
                }
            }
        }
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }
}