<?php

namespace Drupal\rsvplist\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides an RSVP Email form
 * */

class RSVPForm extends FormBase {
  /**
   * (@inheritdoc)
   *
   */
  public function getFormId() {
    return 'rsvplist_email_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->nid->value;
    $form['email'] = array(
      '#title' => t('Email address'),
      '#type' => 'textfield',
      '#size' => 25,
      '#description' => t("We'll send updates to the email address you provide."),
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('RSVP'),
    );
    $form['nid'] = array(
      '#type' => 'hidden',
      '#value' => $nid,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('email');
    if ($value == !\Drupal::service('email.validator')->isValid($value)) {
      $form_state->setErrorByName('email', t('The email address %mail is not valid.', array('%mail' => $value)));
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

    $query = \Drupal::database()->insert('rsvplist');
    $query->fields(array(
      'mail',
      'nid',
      'uid',
      'created',
    ));
    $query->values(array(
        $form_state->getValue('email'),
        $form_state->getValue('nid'),
        $user->id(),
        time(),
      )
    );
    $query->execute();

    drupal_set_message(t('Thank you for your RSVP, you are on the list for the event.'));
  }
}