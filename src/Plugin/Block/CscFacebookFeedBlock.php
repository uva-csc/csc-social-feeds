<?php

namespace Drupal\csc_social_feeds\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a CSC social media feed block.
 *
 * @Block(
 *   id = "csc_facebook_feed",
 *   admin_label = @Translation("CSC Social Feeds Facebook Block"),
 * )
 */
class CscFacebookFeedBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    // csc_log("In build for fb block");
    $data = csc_social_feeds_get_posts();

    $entries = array();
    foreach($data as $post) {
      if (!empty($post['created_time']) && !empty($post['message'])) {
        $dt = new \DateTime($post['created_time']);
        $ddt = $dt->format('M d, Y');
        $msg = $post['message'];
        $truncmsg = substr($msg, 0, 150);
        $truncmsg = substr($truncmsg, 0, strripos($truncmsg, ' ')) . '....';
        // $idval = $post['id'];
        $purl = $post['permalink_url'];
        $entry = <<<EOM
              <div class="entry">
                <a href="$purl" target="_blank" class="plain">
                  <span class="date">UVACSC – $ddt</span>
                  <span class="message">$truncmsg</span>
                </a>
              </div>
        EOM;

        $entries[] = $entry;
        // How to get photos and links from FB post
        /*$attm = !empty($post['attachments']['data']) ? $post['attachments']['data'][0] : false;
        if (!$attm) {
          $entry .= '</div>';
        } elseif ($attm['type'] == 'photo') {
          $entry .= <<<EOM
                <span class="photo">
                  <img src="{$attm['media']['image']['src']}" height="100" />
                </span>
          EOM;
        } elseif ($attm['type'] == 'share') {
          $entry .= <<<EOM
                <span class="link"><a href="{$attm['url']}" target="_blank">{$attm['title']}</a></span>
              </div>
          EOM;
        }*/
      }
    }

    // Return the text to be displayed in the block.
    return [
      '#theme' => 'csc_facebook_feed',
      '#cache' => [
        'max-age' => 0,
      ],
      '#markup' => implode('', $entries),
      // '#markup' => '<ul>' . implode('', $listmu) . '</ul>',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    return AccessResult::allowed();
  }

}
