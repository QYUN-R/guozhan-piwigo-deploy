{combine_css id='standard_pages_css' path="themes/standard_pages/skins/{$STD_PGS_SELECTED_SKIN}.css" order=100}
{combine_css id='guozhan_login_css' path="themes/standard_pages/guozhan-login.css" order=200}
{combine_css path="themes/default/vendor/fontello/css/gallery-icon.css" order=-10}

<script>
  var selected_language = `{$language_options[$current_language]}`;
  var url_logo_light = `{$ROOT_URL}themes/standard_pages/images/piwigo_logo.svg`;
  var url_logo_dark = `{$ROOT_URL}themes/standard_pages/images/piwigo_logo_dark.svg`;
</script>
{combine_script id='standard_pages_js' load='async' require='jquery' path='themes/standard_pages/js/standard_pages.js'}

<div id="mode" class="light guozhan-login-page">
  <header class="guozhan-login-top">
    <a class="guozhan-login-brand" href="{$ROOT_URL}index.php" aria-label="返回图库">
      <img src="{$ROOT_URL}themes/guozhan-gallery/assets/logo-mark.png" alt="">
      <span>
        <strong>图物计划国展素材馆</strong>
        <small>AI Graphics Learning Plan</small>
      </span>
    </a>
    <nav class="guozhan-login-links" aria-label="登录页辅助链接">
      <a href="{$ROOT_URL}index.php">返回图库</a>
      <a href="{$HELP_LINK}" target="_blank" rel="noopener">{'Help'|translate}</a>
    </nav>
  </header>

  <main class="guozhan-login-shell">
    <section class="guozhan-login-visual" aria-label="后台入口说明">
      <div class="guozhan-login-art"></div>
      <div class="guozhan-login-copy">
        <p>客户后台入口</p>
        <h1>管理作品、分类、比赛与客服信息</h1>
        <div class="guozhan-login-points" aria-label="后台功能">
          <span>作品上传</span>
          <span>分类同步</span>
          <span>比赛筛选</span>
          <span>品牌客服</span>
        </div>
      </div>
    </section>

    <section id="login-form" class="guozhan-login-panel" aria-label="登录表单">
      <div class="guozhan-login-panel-head">
        <p>Sign in</p>
        <h2>{'Login'|translate}</h2>
        <span>使用管理员账号进入国展客户后台。</span>
      </div>

{if isset($errors['login_page_error'])}
      <div class="guozhan-login-alert" role="alert">
  {foreach from=$errors['login_page_error'] item=error}
        <p>{$error}</p>
  {/foreach}
      </div>
{/if}

      <form class="properties guozhan-login-form" action="{$F_LOGIN_ACTION}" method="post" name="login_form">
        <div class="column-flex guozhan-login-field">
          <label for="username">{'Username'|translate}</label>
          <div class="row-flex input-container">
            <i class="gallery-icon-user"></i>
            <input tabindex="1" type="text" name="username" id="username" size="25" autocomplete="username" autofocus data-required="true">
          </div>
          <p class="error-message"><i class="gallery-icon-attention-circled"></i> {'must not be empty'|translate}</p>
        </div>

        <div class="column-flex guozhan-login-field">
          <label for="password">{'Password'|translate}</label>
          <div class="row-flex input-container">
            <i class="gallery-icon-lock"></i>
            <input tabindex="2" type="password" name="password" id="password" size="25" autocomplete="current-password" data-required="true">
            <i class="gallery-icon-eye togglePassword" aria-hidden="true"></i>
          </div>
          <p class="error-message"><i class="gallery-icon-attention-circled"></i> {'must not be empty'|translate}</p>
        </div>

{if $authorize_remembering}
        <div class="column-flex guozhan-login-remember">
          <div class="row-flex remember-me-container">
            <label for="remember_me">
              <input tabindex="3" type="checkbox" name="remember_me" id="remember_me" value="1">
              <span class="gallery-icon-checkmark"></span>
              {'Auto login'|@translate}
            </label>
          </div>
        </div>
{/if}

        <div class="column-flex guozhan-login-submit">
          <input type="hidden" name="redirect" value="{$U_REDIRECT|@urlencode}">
          <input tabindex="4" type="submit" name="login" value="进入后台" class="btn btn-main">
{if isset($errors['login_form_error'])}
          <p class="error-message guozhan-login-form-error" style="display:block;"><i class="gallery-icon-attention-circled"></i> {$errors['login_form_error']}</p>
{/if}
        </div>
      </form>

      <div class="secondary-links guozhan-login-secondary">
{if isset($U_REGISTER)}
        <a href="{$U_REGISTER}" title="{'Register'|translate}">{'Create an account'|translate}</a>
        <span id="separator"></span>
{/if}
{if isset($U_LOST_PASSWORD)}
        <a href="{$U_LOST_PASSWORD}" title="{'Forgot your password?'|translate}">{'Forgot your password?'|translate}</a>
{/if}
      </div>
    </section>
  </main>

{if count($language_options) > 1}
  <section id="language-switch" class="guozhan-login-language">
    <div id="lang-select">
      <span id="other-languages">
  {foreach from=$language_options key=code item=lang}
        <span id="lang={$code}" onclick="setCookie('lang','{$code}',30)">{$lang}</span>
  {/foreach}
      </span>
      <div id="selected-language-container">
        <i class="gallery-icon-left-chevron"></i><span id="selected-language">{$language_options[$current_language]}</span>
      </div>
    </div>
  </section>
{/if}
</div>
