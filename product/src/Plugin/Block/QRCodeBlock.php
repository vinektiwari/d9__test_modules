<?php

namespace Drupal\product\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Render\Markup;
use chillerlan\QRCode\QRCode;
/**
 * Provides a 'QRCodeBlock' block.
 *
 * @Block(
 *  id = "qrcode",
 *  admin_label = @Translation("QRCodeBlock"),
 * )
 */
class QRCodeBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var RouteMatchInterface $routeMatch
   */
  protected RouteMatchInterface $routeMatch;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param RouteMatchInterface $route_match
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $node = $this->routeMatch->getParameter('node');
    if (empty($node)) {
      $build['qrcode_block'] = [
        '#type' => 'container',
        'message' => [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t('QA code can only be shown on product page'),
          '#attributes' => [
            'class' => 'error'
          ]
        ]
      ];

      return $build;
    }
    if ($node instanceof \Drupal\node\NodeInterface && $node->bundle() !== 'product') {

      $build['qrcode_block'] = [
        '#type' => 'container',
        'message' => [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t('QA code can only be shown on product page'),
          '#attributes' => [
            'class' => 'error'
          ]
        ]
      ];

      return $build;
    }

    $data = $node->get('field_app_purchase_link')->getValue();
    $base_64_data = (new QRCode)->render($data[0]['uri']);
    $markup = '<img src="' . $base_64_data . '" ';
    $markup .= 'height="100"  width="100"';
    $markup .= '/>';
    $markup = Markup::create(render($markup));
    $build['qrcode_block']['fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Scan here on your mobile'),
      '#collapsible' => FALSE,
    ];
    $build['qrcode_block']['fieldset']['hero_text'] = [
      '#markup' =>
      '<p>' .
        $this->t('To purchase this product on your app to avail exclusive app only.')
      .'</p>',
      '#weight' => 0,
    ];
    $build['qrcode_block']['fieldset']['qrcode']['#markup'] = $markup;

    return $build;
  }

    /**
     * {@inheritdoc}
     */
    public function getCacheMaxAge() {
        return 0;
    }

}
