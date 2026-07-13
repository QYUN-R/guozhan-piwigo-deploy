{combine_css id='standard_pages_css' path="themes/standard_pages/skins/{$STD_PGS_SELECTED_SKIN}.css" order=100}
{combine_css id='guozhan_login_css' path="themes/standard_pages/guozhan-login.css" order=200}
{combine_css path="themes/default/vendor/fontello/css/gallery-icon.css" order=-10}
{html_head}
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="color-scheme" content="light only">
{/html_head}

<script>
  var selected_language = "{$language_options[$current_language]|escape:'javascript'}";
  var url_logo_light = "{$ROOT_URL|escape:'javascript'}themes/standard_pages/images/piwigo_logo.svg";
  var url_logo_dark = "{$ROOT_URL|escape:'javascript'}themes/standard_pages/images/piwigo_logo_dark.svg";
</script>
{combine_script id='standard_pages_js' load='async' require='jquery' path='themes/standard_pages/js/standard_pages.js'}

<div id="mode" class="light guozhan-login-page">
  <header class="guozhan-login-top">
    <a class="guozhan-login-brand" href="{$ROOT_URL|escape:'html'}index.php" aria-label="返回图库">
      <img src="{$ROOT_URL|escape:'html'}themes/guozhan-gallery/assets/logo-mark.png" alt="">
      <span>
        <strong>图物计划国展素材馆</strong>
        <small>AI Graphics Learning Plan</small>
      </span>
    </a>
    <nav class="guozhan-login-links" aria-label="登录页辅助链接">
      <a href="{$ROOT_URL|escape:'html'}index.php">返回图库</a>
      <a href="{$HELP_LINK|escape:'html'}" target="_blank" rel="noopener noreferrer">{'Help'|translate|escape:'html'}</a>
    </nav>
  </header>

  <main class="guozhan-login-shell">
    <section class="guozhan-login-visual" aria-label="后台入口说明">
      <div class="guozhan-login-copy">
        <p>图物计划 · 客户内容管理</p>
        <h1>让作品管理与前台展示保持同步</h1>
        <span class="guozhan-login-intro">上传作品、维护分类与轮播、更新品牌和客服信息。</span>
        <div class="guozhan-login-points" aria-label="后台功能">
          <span>作品管理</span>
          <span>分类同步</span>
          <span>首页轮播</span>
          <span>品牌客服</span>
        </div>
      </div>
    </section>

    <section id="login-form" class="guozhan-login-panel" aria-label="登录表单">
      <div class="guozhan-login-panel-head">
        <p>Administrator access</p>
        <h2>管理员登录</h2>
        <span>仅允许已授权管理员登录，登录状态最多保留 7 天。</span>
      </div>

{if isset($GZCA_AUTH_NOTICE) and $GZCA_AUTH_NOTICE}
      <div class="guozhan-login-alert" role="status">
        <p>{$GZCA_AUTH_NOTICE|escape:'html'}</p>
      </div>
{/if}

{if isset($errors['login_page_error'])}
      <div class="guozhan-login-alert" role="alert">
  {foreach from=$errors['login_page_error'] item=error}
        <p>{$error|escape:'html'}</p>
  {/foreach}
      </div>
{/if}

      <form class="properties guozhan-login-form" action="{$F_LOGIN_ACTION|escape:'html'}" method="post" name="login_form">
        <div class="column-flex guozhan-login-field">
          <label for="username">{'Username'|translate|escape:'html'}</label>
          <div class="row-flex input-container">
            <i class="gallery-icon-user"></i>
            <input tabindex="1" type="text" name="username" id="username" size="25" autocomplete="username" autofocus data-required="true">
          </div>
          <p class="error-message"><i class="gallery-icon-attention-circled"></i> {'must not be empty'|translate|escape:'html'}</p>
        </div>

        <div class="column-flex guozhan-login-field">
          <label for="password">{'Password'|translate|escape:'html'}</label>
          <div class="row-flex input-container">
            <i class="gallery-icon-lock"></i>
            <input tabindex="2" type="password" name="password" id="password" size="25" autocomplete="current-password" data-required="true">
            <i class="gallery-icon-eye togglePassword" aria-hidden="true"></i>
          </div>
          <p class="error-message"><i class="gallery-icon-attention-circled"></i> {'must not be empty'|translate|escape:'html'}</p>
        </div>

{if $authorize_remembering}
        <div class="column-flex guozhan-login-remember">
          <div class="row-flex remember-me-container">
            <label for="remember_me">
              <input tabindex="3" type="checkbox" name="remember_me" id="remember_me" value="1" checked>
              <span class="gallery-icon-checkmark"></span>
              保持登录 7 天
            </label>
          </div>
        </div>
{/if}

        <div class="column-flex guozhan-login-submit">
          <input type="hidden" name="redirect" value="{$U_REDIRECT|@urlencode|escape:'html'}">
          <input tabindex="4" type="submit" name="login" value="进入后台" class="btn btn-main">
{if isset($errors['login_form_error'])}
          <p class="error-message guozhan-login-form-error" style="display:block;"><i class="gallery-icon-attention-circled"></i> {$errors['login_form_error']|escape:'html'}</p>
{/if}
        </div>
      </form>

      <div class="secondary-links guozhan-login-secondary">
{if isset($U_REGISTER)}
        <a href="{$U_REGISTER|escape:'html'}" title="{'Register'|translate|escape:'html'}">{'Create an account'|translate|escape:'html'}</a>
        <span id="separator"></span>
{/if}
{if isset($U_LOST_PASSWORD)}
        <a href="{$U_LOST_PASSWORD|escape:'html'}" title="{'Forgot your password?'|translate|escape:'html'}">{'Forgot your password?'|translate|escape:'html'}</a>
{/if}
      </div>
    </section>
  </main>

{if count($language_options) > 1}
  <section id="language-switch" class="guozhan-login-language">
    <div id="lang-select">
      <span id="other-languages">
  {foreach from=$language_options key=code item=lang}
        <span id="lang={$code|escape:'html'}" onclick="setCookie('lang','{$code|escape:'javascript'}',30)">{$lang|escape:'html'}</span>
  {/foreach}
      </span>
      <div id="selected-language-container">
        <i class="gallery-icon-left-chevron"></i><span id="selected-language">{$language_options[$current_language]|escape:'html'}</span>
      </div>
    </div>
  </section>
{/if}
</div>
