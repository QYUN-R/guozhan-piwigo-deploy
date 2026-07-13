{combine_css id='standard_pages_css' path="themes/standard_pages/skins/{$STD_PGS_SELECTED_SKIN}.css" order=100}
{combine_css id='guozhan_password_css' path="themes/standard_pages/guozhan-password.css" order=200}
{combine_css path="themes/default/vendor/fontello/css/gallery-icon.css" order=-10}
{html_head}
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="color-scheme" content="light only">
  <meta name="referrer" content="no-referrer">
{/html_head}

<script>
  var selected_language = "{$language_options[$current_language]|escape:'javascript'}";
  var url_logo_light = "{$ROOT_URL|escape:'javascript'}themes/guozhan-gallery/assets/logo-mark.png";
  var url_logo_dark = "{$ROOT_URL|escape:'javascript'}themes/guozhan-gallery/assets/logo-mark.png";
</script>
{combine_script id='standard_pages_js' load='async' require='jquery' path='themes/standard_pages/js/standard_pages.js'}

<div id="mode" class="light guozhan-password-page">
  <header class="guozhan-password-top">
    <a class="guozhan-login-brand" href="{$ROOT_URL|escape:'html'}index.php" aria-label="返回图库">
      <img src="{$ROOT_URL|escape:'html'}themes/guozhan-gallery/assets/logo-mark.png" alt="">
      <span><strong>图物计划国展素材馆</strong><small>AI Graphics Learning Plan</small></span>
    </a>
    <nav class="guozhan-password-links" aria-label="找回密码辅助链接">
      <a href="{$ROOT_URL|escape:'html'}identification.php">返回登录</a>
      <a href="{$ROOT_URL|escape:'html'}index.php">查看图库</a>
    </nav>
  </header>

  <main class="guozhan-password-shell">
    <section id="password-form" class="guozhan-password-panel" aria-label="密码找回表单">
      <div class="guozhan-password-panel-head">
        {if $action eq 'lost'}<h2>找回管理员密码</h2><span>重置链接将发送至已验证的恢复邮箱</span>{/if}
        {if $action eq 'sent'}<h2>查收重置邮件</h2><span>链接仅可使用一次，15 分钟内有效</span>{/if}
        {if $action eq 'lost_code'}<h2>验证邮箱验证码</h2><span>输入邮件中的六位数字</span>{/if}
        {if $action eq 'reset'}<h2>{if isset($is_first_login)}设置管理员密码{else}设置新密码{/if}</h2><span>至少 12 位，包含字母和数字</span>{/if}
        {if $action eq 'reset_end'}<h2>密码更新完成</h2><span>请使用新密码重新登录</span>{/if}
        {if $action eq 'none'}<h2>链接不可用</h2><span>请重新发起找回密码</span>{/if}
      </div>

      {if isset($errors['password_page_error'])}
        <div class="guozhan-password-alert is-error" role="alert">
          {foreach from=$errors['password_page_error'] item=error}<p>{$error|escape:'html'}</p>{/foreach}
        </div>
      {/if}

      {if $action eq 'lost' or $action eq 'reset' or $action eq 'lost_code'}
        <form class="properties guozhan-password-form" action="{$form_action|escape:'html'}?action={$action|escape:'html'}{if isset($key)}&amp;key={$key|escape:'html'}{/if}" method="post">
          <input type="hidden" name="pwg_token" value="{$PWG_TOKEN|escape:'html'}">

          {if $action eq 'lost'}
            <label class="guozhan-password-field" for="username_or_email">
              <span>管理员账号或恢复邮箱</span>
              <span class="guozhan-password-input"><i class="gallery-icon-user-2"></i><input type="text" id="username_or_email" name="username_or_email" maxlength="100" value="{if isset($username_or_email)}{$username_or_email|escape:'html'}{/if}" autocomplete="username" autofocus required></span>
            </label>
            <button type="submit" name="submit" class="guozhan-password-submit">发送重置链接</button>
          {elseif $action eq 'lost_code'}
            <div class="guozhan-password-alert is-success" role="status"><p>如果账号存在，验证码已发送到已验证邮箱。</p></div>
            <label class="guozhan-password-field" for="user_code">
              <span>六位验证码</span>
              <span class="guozhan-password-input"><i class="gallery-icon-lock"></i><input type="text" id="user_code" name="user_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" autofocus required></span>
            </label>
            <button type="submit" name="submit" class="guozhan-password-submit">验证验证码</button>
          {elseif $action eq 'reset'}
            <p class="guozhan-password-intro">{if isset($is_first_login)}首次使用请设置一个仅客户掌握的管理员密码。{else}验证已通过，请设置新的管理员密码。{/if}</p>
            {if isset($is_first_login)}
              <label class="guozhan-password-field" for="username"><span>管理员账号</span><span class="guozhan-password-input"><i class="gallery-icon-user-2"></i><input type="text" id="username" value="{$username|escape:'html'}" disabled></span></label>
            {/if}
            <label class="guozhan-password-field" for="use_new_pwd">
              <span>新密码</span>
              <span class="guozhan-password-input"><i class="gallery-icon-lock"></i><input type="password" name="use_new_pwd" id="use_new_pwd" minlength="12" maxlength="128" autocomplete="new-password" autofocus required><i class="gallery-icon-eye togglePassword"></i></span>
            </label>
            <label class="guozhan-password-field" for="passwordConf">
              <span>确认新密码</span>
              <span class="guozhan-password-input"><i class="gallery-icon-lock"></i><input type="password" name="passwordConf" id="passwordConf" minlength="12" maxlength="128" autocomplete="new-password" required><i class="gallery-icon-eye togglePassword"></i></span>
            </label>
            <button type="submit" name="submit" class="guozhan-password-submit">确认并更新密码</button>
          {/if}

          {if isset($errors['password_form_error'])}
            <div class="guozhan-password-alert is-error" role="alert"><p>{$errors['password_form_error']|escape:'html'}</p></div>
          {/if}
        </form>
      {elseif $action eq 'sent'}
        <div class="guozhan-password-result is-success"><span>✓</span><strong>请检查恢复邮箱</strong><p>如果账号存在且邮箱已经验证，系统会发送一封密码重置邮件。新链接会使旧链接失效。</p><a href="{$ROOT_URL|escape:'html'}identification.php">返回登录</a></div>
      {elseif $action eq 'reset_end'}
        <div class="guozhan-password-result is-success"><span>✓</span><strong>管理员密码已更新</strong><p>旧登录状态和访问密钥已经失效，请重新验证身份。</p><a href="{$ROOT_URL|escape:'html'}identification.php">返回登录</a></div>
      {else}
        <div class="guozhan-password-result is-error"><span>!</span><strong>当前找回链接无效或已过期</strong><p>请返回登录页重新发送一次性重置链接。</p><a href="{$ROOT_URL|escape:'html'}password.php?action=lost">重新找回密码</a></div>
      {/if}

      <div class="guozhan-password-secondary"><a href="{$ROOT_URL|escape:'html'}identification.php">返回管理员登录</a></div>
    </section>
  </main>
</div>
