<?php

declare(strict_types=1);

namespace Pollen\WpTb;

use Pollen\WpTb\Contracts\WpTbContract;
use Psr\Container\ContainerInterface as Container;
use RuntimeException;
use tiFy\Contracts\Filesystem\LocalFilesystem;
use tiFy\Support\Concerns\BootableTrait;
use tiFy\Support\Concerns\ContainerAwareTrait;
use tiFy\Support\ParamsBag;
use tiFy\Support\Proxy\Storage;
use WP_Admin_Bar;

class WpTb implements WpTbContract
{
    use BootableTrait;
    use ContainerAwareTrait;

    /**
     * Instance de la classe.
     * @var static|null
     */
    private static $instance;

    /**
     * Instance du gestionnaire de configuration.
     * @var ParamsBag
     */
    private $configBag;

    /**
     * Instance du gestionnaire des ressources
     * @var LocalFilesystem|null
     */
    private $resources;

    /**
     * @param array $config
     * @param Container|null $container
     *
     * @return void
     */
    public function __construct(array $config = [], Container $container = null)
    {
        $this->setConfig($config);

        if (!is_null($container)) {
            $this->setContainer($container);
        }

        if (!self::$instance instanceof static) {
            self::$instance = $this;
        }
    }

    /**
     * @inheritDoc
     */
    public static function instance(): WpTbContract
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }
        throw new RuntimeException(sprintf('Unavailable %s instance', __CLASS__));
    }


    /**
     * @inheritDoc
     */
    public function boot(): WpTbContract
    {
        if (!$this->isBooted()) {
            events()->trigger('wp-tb.booting', [$this]);

            // - Balises de site.
            add_action('wp_head', function () {
                echo "<!-- TigreBlanc Copyright -->";
                echo '<meta name="author" content="TigreBlanc">';
                echo '<meta name="designer" content="TigreBlanc">';
                echo '<meta name="copyright" content="© TigreBlanc" />';
                echo "<!-- / TigreBlanc Copyright -->";
            }, 1);

            // - Personnalisation du logo de la barre d'administration et des sous entrées de menu.
            add_action(
                'admin_bar_menu',
                function (WP_Admin_Bar $wp_admin_bar) {
                    $admin_bar_menu_logo = [
                        [
                            'id'    => 'tb-logo',
                            'title' => '<span class="tigreblanc-logo" style="font-size:35px;"></span>',
                            'href'  => 'https://www.tigreblanc.fr',
                            'meta'  => [
                                'title'  => __('A propos de Tigre Blanc', 'wp-tb'),
                                'target' => '_blank',
                            ],
                        ],
                        [
                            'id'     => 'tb-logo-site',
                            'parent' => 'tb-logo',
                            'title'  => __('Site Officiel de Tigre Blanc', 'wp-tb'),
                            'href'   => 'https://www.tigreblanc.fr',
                        ],
                        [
                            'id'     => 'tb-logo-external',
                            'parent' => 'tb-logo',
                            'group'  => true,
                            'meta'   => [
                                'class' => 'ab-sub-secondary',
                            ],
                        ],
                        [
                            'id'     => 'tb-logo-facebook',
                            'parent' => 'tb-logo-external',
                            'title'  => __('Page Facebook', 'wp-tb'),
                            'href'   => 'https://www.facebook.com/tigreblancdouai',
                            'meta'   => [
                                'target' => '_blank',
                            ],
                        ],
                        [
                            'id'     => 'tb-logo-twitter',
                            'parent' => 'tb-logo-external',
                            'title'  => __('Compte Twitter', 'wp-tb'),
                            'href'   => 'https://twitter.com/TigreBlancDouai',
                            'meta'   => [
                                'target' => '_blank',
                            ],
                        ],
                        [
                            'id'     => 'tb-logo-mailing',
                            'parent' => 'tb-logo',
                            'group'  => true,
                            'meta'   => [
                                'class' => 'ab-sub-primary',
                            ],
                        ],
                        [
                            'id'     => 'tb-logo-mailing-contact',
                            'parent' => 'tb-logo-mailing',
                            'title'  => __('Contact l\'agence', 'wp-tb'),
                            'href'   => 'mailto:contact@tigreblanc.fr',
                        ],
                        [
                            'id'     => 'tb-logo-mailing-support',
                            'parent' => 'tb-logo-mailing',
                            'title'  => __('Support Technique', 'wp-tb'),
                            'href'   => 'mailto:support@tigreblanc.fr',
                        ],
                    ];

                    $wp_admin_bar->remove_menu('wp-logo');

                    foreach ($admin_bar_menu_logo as $node) {
                        if (!empty($node['group'])) {
                            $wp_admin_bar->add_group($node);
                        } else {
                            $wp_admin_bar->add_menu($node);
                        }
                    }
                },
                11
            );

            // - Personnalisation du pied de page de l'interface d'administration.
            add_filter(
                'admin_footer_text',
                function () {
                    return sprintf(
                        __('Merci de faire de %s le partenaire de votre communication digitale', 'wp-tb'),
                        "<a class=\"tigreblanc-logo\" 
                    href=\"https://www.tigreblanc.fr\" 
                    title=\"Tigre Blanc | Agence de communication, agence web à Douai\"
                    style=\"font-size:40px; 
                    vertical-align:middle; 
                    display:inline-block;\" 
                    target=\"_blank\"
                    >
                </a>"
                    );
                },
                999999
            );

            // - Url du logo de l'interface de connection de Wordpress.
            add_filter(
                'login_headerurl',
                function () {
                    return home_url('/');
                }
            );

            // - Intitlulé du lien de l'interface de connection de Wordpress.
            add_filter(
                'login_headertext',
                function () {
                    return get_bloginfo('name') . ' | ' . get_bloginfo('description');
                }
            );
            
            $this->setBooted();

            events()->trigger('wp-tb.booted', [$this]);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function config($key = null, $default = null)
    {
        if (!isset($this->configBag) || is_null($this->configBag)) {
            $this->configBag = new ParamsBag();
        }

        if (is_string($key)) {
            return $this->configBag->get($key, $default);
        } elseif (is_array($key)) {
            return $this->configBag->set($key);
        } else {
            return $this->configBag;
        }
    }

    /**
     * @inheritDoc
     */
    public function resources(?string $path = null)
    {
        if (!isset($this->resources) || is_null($this->resources)) {
            $this->resources = Storage::local(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'resources');
        }

        return is_null($path) ? $this->resources : $this->resources->path($path);
    }

    /**
     * @inheritDoc
     */
    public function setConfig(array $attrs): WpTbContract
    {
        $this->config($attrs);

        return $this;
    }
}
