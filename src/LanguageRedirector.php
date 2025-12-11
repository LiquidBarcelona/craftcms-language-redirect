<?php
namespace liquidbcn\languageredirect;

use Craft;
use craft\base\Model;
use liquidbcn\languageredirect\models\Settings;
use yii\web\HttpException;

class LanguageRedirector extends \craft\base\Plugin
{
    public bool $hasCpSettings = true;

    public function init()
    {
        parent::init();
        $this->redirect();
    }

    protected function redirect() {

        $browser = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;

        $default = $this->getSettings()->defaultLanguage;
        $urls = $this->getSettings()->urls;

        if(!empty($urls) && isset($_SERVER['REQUEST_URI'])) {
            $browserLoc = new \koenster\PHPLanguageDetection\BrowserLocalization();
            $browserLoc->setAvailable(array_keys($urls))
                ->setDefault($default)
                ->setPreferences($browser);
            $url = parse_url($_SERVER['REQUEST_URI']);

            if ($_SERVER['REQUEST_METHOD'] === 'GET') {

                if(empty($url['path']) || $url['path'] === '/') {
                    $language = $browserLoc->detect();
                    if(isset($urls[$language])) {
                        header('HTTP/1.1 301 Moved Permanently');
                        header('Location: ' . $urls[$language]);
                        exit();
                    }
                }
            }
        }
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'language-redirect/settings',
            [
                'settings'  => $this->getSettings(),
            ]
        );
    }
}
