{html_head}
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
{/html_head}
{combine_css path=$GZCA_PATH|cat:'assets/admin.css'}
{combine_script id='gzca.admin' load='footer' require='jquery' path=$GZCA_PATH|cat:'assets/admin.js'}

<div class="gzca-shell" data-gzca-tab="{$GZCA_TAB|escape:'html'}">
  <aside class="gzca-sidebar">
    <a class="gzca-brand" href="{$GZCA_URLS.dashboard|escape:'html'}">
      <span class="gzca-brand-mark">图</span>
      <span><strong>图物计划</strong><small>国展素材馆后台</small></span>
    </a>

    <nav class="gzca-nav" aria-label="客户后台导航">
      <a href="{$GZCA_URLS.dashboard|escape:'html'}" class="{if $GZCA_TAB eq 'dashboard'}is-active{/if}"><span>概</span>工作台</a>
      <a href="{$GZCA_URLS.home|escape:'html'}" class="{if $GZCA_TAB eq 'home'}is-active{/if}"><span>首</span>首页轮播</a>
      <a href="{$GZCA_URLS.upload|escape:'html'}" class="{if $GZCA_TAB eq 'upload'}is-active{/if}"><span>传</span>上传作品</a>
      <a href="{$GZCA_URLS.works|escape:'html'}" class="{if $GZCA_TAB eq 'works'}is-active{/if}"><span>图</span>作品管理</a>
      <a href="{$GZCA_URLS.categories|escape:'html'}" class="{if $GZCA_TAB eq 'categories'}is-active{/if}"><span>类</span>分类管理</a>
      <a href="{$GZCA_URLS.contact|escape:'html'}" class="{if $GZCA_TAB eq 'contact'}is-active{/if}"><span>牌</span>品牌与客服</a>
      <a href="{$GZCA_URLS.security|escape:'html'}" class="{if $GZCA_TAB eq 'security'}is-active{/if}"><span>安</span>账号中心</a>
    </nav>

    <div class="gzca-sidebar-footer">
      <a href="{$GZCA_URLS.gallery|escape:'html'}" target="_blank" rel="noopener">查看网站前台</a>
      <small>插件版本 {$GZCA_VERSION}</small>
    </div>
  </aside>

  <main class="gzca-main">
    <header class="gzca-page-header {if $GZCA_TAB eq 'security'}gzca-page-header-account{/if}">
      <div>
        {if $GZCA_TAB eq 'dashboard'}<p>内容总览</p><h2>工作台</h2>{/if}
        {if $GZCA_TAB eq 'home'}<p>首页视觉与轮播壁纸</p><h2>首页轮播</h2>{/if}
        {if $GZCA_TAB eq 'home'}
      <form class="gzca-home-hero-layout" action="{$GZCA_URLS.home|escape:'html'}" method="post" enctype="multipart/form-data">
        <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="save_home_hero">
        <section class="gzca-panel">
          <div class="gzca-panel-head"><div><p>首页第一屏</p><h3>轮播壁纸管理</h3></div><span class="gzca-help">最多 5 个轮播位</span></div>
          <div class="gzca-hero-manager">
            {foreach from=$GZCA_HERO_SLIDES item=slide}
              <article class="gzca-hero-slide-card {if !$slide.enabled}is-disabled{/if}">
                <div class="gzca-hero-preview">{if $slide.url}<img src="{$slide.url|escape:'html'}" alt="{$slide.title|escape:'html'}">{else}<span>未设置图片</span>{/if}</div>
                <div class="gzca-hero-fields">
                  <div class="gzca-hero-card-head"><strong>轮播位 {$slide.index+1}</strong><span class="gzca-status {if $slide.enabled}is-online{else}is-offline{/if}">{if $slide.enabled}启用中{else}已停用{/if}</span></div>
                  <label class="gzca-check"><input type="checkbox" name="hero_enabled[{$slide.index}]" value="1" {if $slide.enabled}checked{/if}><span><strong>启用这个轮播位</strong><small>停用后前台不会播放这张图，图片文件会保留。</small></span></label>
                  <label class="gzca-field"><span>后台识别名称</span><input type="text" name="hero_title[{$slide.index}]" value="{$slide.title|escape:'html'}" maxlength="80"></label>
                  <label class="gzca-field"><span>图片焦点位置</span><select name="hero_position[{$slide.index}]"><option value="center" {if $slide.position eq 'center'}selected{/if}>居中</option><option value="center top" {if $slide.position eq 'center top'}selected{/if}>居上</option><option value="center bottom" {if $slide.position eq 'center bottom'}selected{/if}>居下</option><option value="left center" {if $slide.position eq 'left center'}selected{/if}>偏左</option><option value="right center" {if $slide.position eq 'right center'}selected{/if}>偏右</option></select></label>
                  <label class="gzca-field"><span>替换壁纸</span><input type="file" name="hero_image_{$slide.index}" accept="image/jpeg,image/png,image/webp"><small>建议横图，JPG/PNG/WebP，最大 8MB；过大的图片会自动压缩。</small></label>
                  <label class="gzca-check"><input type="checkbox" name="hero_clear[{$slide.index}]" value="1"><span><strong>{if $slide.default_url}恢复默认壁纸{else}清空这个预留位{/if}</strong><small>{if $slide.default_url}恢复到系统默认首页图。{else}清空后这个轮播位会停用。{/if}</small></span></label>
                </div>
              </article>
            {/foreach}
          </div>
        </section>
        <aside class="gzca-panel gzca-home-help-panel">
          <div class="gzca-panel-head"><div><p>管理建议</p><h3>适合大量内容的后台结构</h3></div></div>
          <div class="gzca-home-guide">
            <div><strong>首页轮播</strong><span>只管理第一屏视觉图，不混入作品库。</span></div>
            <div><strong>上传作品</strong><span>大量图片走自动分批上传队列。</span></div>
            <div><strong>作品管理</strong><span>负责上架、下架、分类、排序和批量移动。</span></div>
            <div><strong>分类管理</strong><span>负责板块增删、隐藏、分页筛选。</span></div>
            <div><strong>品牌客服</strong><span>负责 Logo、二维码、微信和联系方式。</span></div>
          </div>
          <button class="gzca-button gzca-button-primary gzca-button-block" type="submit">保存首页轮播</button>
        </aside>
      </form>
    {/if}

    {if $GZCA_TAB eq 'upload'}<p>添加图库内容</p><h2>上传作品</h2>{/if}
        {if $GZCA_TAB eq 'works'}<p>编号、封面与上下架</p><h2>作品管理</h2>{/if}
        {if $GZCA_TAB eq 'categories'}<p>主分类与小分类</p><h2>分类管理</h2>{/if}
        {if $GZCA_TAB eq 'contact'}<p>同步前台 Logo 与联系方式</p><h2>品牌与客服</h2>{/if}
        {if $GZCA_TAB eq 'security'}<p>管理员资料与安全设置</p><h2>个人中心</h2>{/if}
      </div>
      <div class="gzca-header-actions">
        <a class="gzca-admin-session" href="{$GZCA_URLS.security|escape:'html'}" aria-label="查看管理员账号信息" {if $GZCA_TAB eq 'security'}aria-current="page"{/if}>
          <span class="gzca-admin-session-mark">管</span>
          <span class="gzca-admin-session-copy">
            <small>当前{$GZCA_ADMIN_IDENTITY.role|escape:'html'}</small>
            <strong>{$GZCA_ADMIN_IDENTITY.username|escape:'html'}</strong>
            <em>登录有效至 {$GZCA_ADMIN_IDENTITY.expires_at|escape:'html'}</em>
          </span>
        </a>
        <a class="gzca-button gzca-button-quiet" href="{$GZCA_URLS.gallery|escape:'html'}" target="_blank" rel="noopener">预览网站</a>
        {if $GZCA_TAB neq 'upload'}<a class="gzca-button gzca-button-primary" href="{$GZCA_URLS.upload|escape:'html'}">上传作品</a>{/if}
        <a class="gzca-button gzca-button-danger gzca-logout-button" href="{$GZCA_URLS.logout|escape:'html'}">退出登录</a>
      </div>
    </header>

    {if $GZCA_TAB eq 'dashboard'}
      <form class="gzca-setup-bar" action="{$GZCA_URLS.dashboard|escape:'html'}" method="post">
        <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="seed_categories">
        <span><strong>同步前台目录</strong><small>建立 10 个大板块、29 个子板块和稳定标识；重复执行不会覆盖客户改过的名称与排序。</small></span>
        <button class="gzca-button gzca-button-quiet" type="submit">建立前端对应分类</button>
      </form>
      <form class="gzca-setup-bar" action="{$GZCA_URLS.dashboard|escape:'html'}" method="post">
        <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="import_sample_works">
        <span><strong>导入本地样例作品</strong><small>把主题内置的 8 张本地生成作品写入 Piwigo 图库，当前已导入 {$GZCA_SAMPLE_WORKS_STATUS.imported}/{$GZCA_SAMPLE_WORKS_STATUS.total} 张{if $GZCA_SAMPLE_WORKS_STATUS.missing_assets gt 0}，缺少 {$GZCA_SAMPLE_WORKS_STATUS.missing_assets} 个素材文件{/if}。</small></span>
        <button class="gzca-button gzca-button-primary" type="submit" {if $GZCA_SAMPLE_WORKS_STATUS.remaining eq 0}disabled{/if}>{if $GZCA_SAMPLE_WORKS_STATUS.remaining eq 0}样例已导入{else}导入样例作品{/if}</button>
      </form>
      <section class="gzca-stat-grid" aria-label="图库统计">
        <article><span>全部作品</span><strong>{$GZCA_STATS.works}</strong><a href="{$GZCA_URLS.works|escape:'html'}">查看作品</a></article>
        <article><span>已上架</span><strong>{$GZCA_STATS.online}</strong><a href="{$GZCA_URLS.works|escape:'html'}&amp;status=online">查看上架内容</a></article>
        <article><span>已下架</span><strong>{$GZCA_STATS.offline}</strong><a href="{$GZCA_URLS.works|escape:'html'}&amp;status=offline">检查待发布内容</a></article>
        <article><span>作品分类</span><strong>{$GZCA_STATS.categories}</strong><a href="{$GZCA_URLS.categories|escape:'html'}">管理分类</a></article>
      </section>

      <section class="gzca-panel gzca-health-panel">
        <div class="gzca-panel-head">
          <div><p>系统状态</p><h3>数据库与网站联动</h3></div>
          <span class="gzca-health-summary {if $GZCA_DATABASE_HEALTH.ready}is-ok{else}is-warning{/if}">{if $GZCA_DATABASE_HEALTH.ready}全部正常{else}存在待处理项{/if}</span>
        </div>
        <div class="gzca-health-grid">
          <div class="{if $GZCA_DATABASE_HEALTH.connection}is-ok{else}is-warning{/if}"><span>数据库连接</span><strong>{if $GZCA_DATABASE_HEALTH.connection}已连接{else}连接失败{/if}</strong></div>
          <div class="{if $GZCA_DATABASE_HEALTH.works_table}is-ok{else}is-warning{/if}"><span>作品扩展表</span><strong>{if $GZCA_DATABASE_HEALTH.works_table}已创建{else}尚未创建{/if}</strong></div>
          <div class="{if $GZCA_DATABASE_HEALTH.categories_table}is-ok{else}is-warning{/if}"><span>分类扩展表</span><strong>{if $GZCA_DATABASE_HEALTH.categories_table}已创建{else}尚未创建{/if}</strong></div>
          <div class="{if $GZCA_DATABASE_HEALTH.category_contract}is-ok{else}is-warning{/if}"><span>前台板块目录</span><strong>{if $GZCA_DATABASE_HEALTH.category_contract}39 个板块已对齐{else}请点击同步目录{/if}</strong></div>
          <div class="{if $GZCA_DATABASE_HEALTH.upload_contract}is-ok{else}is-warning{/if}"><span>上传落点</span><strong>{if $GZCA_DATABASE_HEALTH.upload_contract}39 个位置已开启{else}请点击同步目录{/if}</strong></div>
          <div class="{if $GZCA_DATABASE_HEALTH.web_services}is-ok{else}is-warning{/if}"><span>公开数据接口</span><strong>{if $GZCA_DATABASE_HEALTH.web_services}已启用{else}尚未启用{/if}</strong></div>
          <div class="{if $GZCA_DATABASE_HEALTH.frontend_sync}is-ok{else}is-warning{/if}"><span>品牌客服同步</span><strong>{if $GZCA_DATABASE_HEALTH.frontend_sync}已开启{else}尚未开启{/if}</strong></div>
          <div class="{if $GZCA_DATABASE_HEALTH.theme_active}is-ok{else}is-warning{/if}"><span>正式网站主题</span><strong>{if $GZCA_DATABASE_HEALTH.theme_active}已启用{else}尚未启用{/if}</strong></div>
        </div>
        <p class="gzca-health-note">数据表前缀：<code>{$GZCA_DATABASE_HEALTH.table_prefix|escape:'html'}</code>。状态来自当前 Piwigo 数据库连接与配置，不需要单独填写数据库密码。</p>
      </section>

      <section class="gzca-panel">
        <div class="gzca-panel-head">
          <div><p>最近更新</p><h3>最新作品</h3></div>
          <a class="gzca-text-link" href="{$GZCA_URLS.works|escape:'html'}">查看全部</a>
        </div>
        {if count($GZCA_RECENT_WORKS) gt 0}
          <div class="gzca-work-strip">
            {foreach from=$GZCA_RECENT_WORKS item=work}
              <a href="{$GZCA_URLS.works|escape:'html'}&amp;edit={$work.id}">
                <img src="{$work.thumb_url|escape:'html'}" alt="{$work.display_name|escape:'html'}">
                <strong>{$work.display_name|escape:'html'}</strong>
                <span>{$work.code|escape:'html'} · {if $work.status eq 'online'}已上架{else}已下架{/if}</span>
              </a>
            {/foreach}
          </div>
        {else}
          <div class="gzca-empty"><strong>图库里还没有作品</strong><span>先创建分类，然后上传第一批作品。</span><a class="gzca-button gzca-button-primary" href="{$GZCA_URLS.upload|escape:'html'}">开始上传</a></div>
        {/if}
      </section>

      <section class="gzca-guide">
        <div><b>1</b><span><strong>维护板块与画展</strong><small>常规分类、专项画展和预留画展都能独立改名、排序与隐藏。</small></span></div>
        <div><b>2</b><span><strong>上传并发布作品</strong><small>选择作品分类后批量上传，默认立即上架并公开展示。</small></span></div>
        <div><b>3</b><span><strong>检查并上架</strong><small>确认标题、编号、封面、公开状态后再展示到网站。</small></span></div>
      </section>
    {/if}

    {if $GZCA_TAB eq 'upload'}
      <form class="gzca-form-layout" action="{$GZCA_URLS.upload|escape:'html'}" method="post" enctype="multipart/form-data" data-upload-form>
        <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}">
        <input type="hidden" name="gzca_action" value="upload_works">

        <section class="gzca-panel gzca-upload-panel">
          <div class="gzca-panel-head"><div><p>第一步</p><h3>选择作品图片</h3></div><span class="gzca-help">自动分批上传</span></div>
          <label class="gzca-dropzone" data-dropzone>
            <input type="file" name="artworks[]" accept="image/jpeg,image/png,image/webp" multiple required data-file-input>
            <span class="gzca-drop-icon">+</span>
            <strong>点击选择，或把图片拖到这里</strong>
            <small>支持 JPG、PNG、WebP；可以一次选择大量图片，系统会自动按小批次上传，避免页面长时间卡死。过大的图片仍会压缩到约 1-3MB。</small>
          </label>
          <div class="gzca-file-list" data-file-list aria-live="polite"></div>
          <div class="gzca-upload-queue" data-upload-progress hidden>
            <div class="gzca-upload-progress-head"><strong data-upload-progress-title>准备上传</strong><span data-upload-progress-count>0 / 0</span></div>
            <progress value="0" max="100" data-upload-progress-bar></progress>
            <div class="gzca-upload-progress-log" data-upload-progress-log aria-live="polite"></div>
          </div>
        </section>

        <aside class="gzca-panel gzca-upload-settings">
          <div class="gzca-panel-head"><div><p>第二步</p><h3>填写发布信息</h3></div><span class="gzca-help">{$GZCA_UPLOAD_TARGET_COUNT} 个可上传位置</span></div>
          <label class="gzca-field">
            <span>所属前台板块</span>
            <select name="album_id" required data-upload-category>
              <option value="">请选择要上传到的前台板块</option>
              {foreach from=$GZCA_CATEGORY_OPTION_GROUPS item=group}
                <optgroup label="{$group.name|escape:'html'}">
                  {foreach from=$group.options item=category}
                    <option value="{$category.id}" data-prefix="{$category.prefix|escape:'html'}" data-path="{$category.name|escape:'html'}">{$category.option_label|escape:'html'} · {$category.prefix|escape:'html'}{if !$category.visible}（当前隐藏）{/if}{if $category.reserved}（预留画展）{/if}</option>
                  {/foreach}
                </optgroup>
              {/foreach}
            </select>
            <small>请选择准确板块：父级板块会在前台“全部作品 / 直接上传”入口显示；子板块会在对应子板块页面显示。</small>
          </label>
          <div class="gzca-upload-selection" data-upload-selection hidden><span>本批作品将上传到</span><strong data-upload-path></strong></div>
          <label class="gzca-field"><span>编号前缀</span><input type="text" name="code_prefix" placeholder="选择分类后自动填写" maxlength="48" data-code-prefix readonly><small>前缀跟随分类，系统自动生成连续编号，避免作品放错目录。</small></label>
          <label class="gzca-field"><span>每批上传数量</span><select name="upload_batch_size" data-upload-batch-size><option value="20" selected>20 张（推荐）</option><option value="30">30 张</option><option value="40">40 张</option><option value="50">50 张（适合小图）</option></select><small>批次会依次处理；超过 72 MB 时系统自动拆小，避免服务器负载过高。</small></label>
          <input type="hidden" name="publish_now" value="0"><label class="gzca-check"><input type="checkbox" name="publish_now" value="1" checked><span><strong>上传后立即上架</strong><small>默认上传后前台可见；取消勾选时先保存为下架状态，检查后再发布。</small></span></label>
          <label class="gzca-check"><input type="checkbox" name="set_cover" value="1"><span><strong>把第一张设为分类封面</strong><small>以后也可以在作品管理里重新设置。</small></span></label>
          <button class="gzca-button gzca-button-primary gzca-button-block" type="submit" data-upload-submit>开始上传作品</button>
        </aside>
      </form>
    {/if}

    {if $GZCA_TAB eq 'works'}
      {if $GZCA_EDIT_WORK}
        <section class="gzca-panel gzca-edit-panel" id="edit-work">
          <div class="gzca-panel-head"><div><p>编辑作品</p><h3>{$GZCA_EDIT_WORK.display_name|escape:'html'}</h3></div><a class="gzca-text-link" href="{$GZCA_URLS.works|escape:'html'}">关闭编辑</a></div>
          <form class="gzca-edit-grid" action="{$GZCA_URLS.works|escape:'html'}&amp;edit={$GZCA_EDIT_WORK.id}#edit-work" method="post">
            <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="save_work"><input type="hidden" name="image_id" value="{$GZCA_EDIT_WORK.id}">
            <div class="gzca-edit-preview"><img src="{$GZCA_EDIT_WORK.thumb_url|escape:'html'}" alt="{$GZCA_EDIT_WORK.display_name|escape:'html'}"><span>{$GZCA_EDIT_WORK.code|escape:'html'}</span></div>
            <div class="gzca-edit-fields">
              {if $GZCA_EDIT_WORK.other_category_links|@count}
                <div class="gzca-alert gzca-alert-warn"><strong>这张图片还出现在其他板块</strong><span>当前选择为“{$GZCA_EDIT_WORK.category_display_name|escape:'html'}”。保存后系统会只保留当前选择的板块。</span><ul>{foreach from=$GZCA_EDIT_WORK.other_category_links item=link}<li>{$link.path|escape:'html'} {if $link.prefix}<em>{$link.prefix|escape:'html'}</em>{/if}</li>{/foreach}</ul></div>
              {/if}
              {if $GZCA_EDIT_WORK.code_prefix_warning}
                <div class="gzca-alert gzca-alert-warn"><strong>作品编号与所属分类不一致</strong><span>{$GZCA_EDIT_WORK.code_prefix_warning|escape:'html'}</span></div>
              {/if}
              <label class="gzca-field"><span>作品名称</span><input type="text" name="title" value="{$GZCA_EDIT_WORK.display_name|escape:'html'}" required></label>
              <label class="gzca-field"><span>作品编号</span><input type="text" name="code" value="{$GZCA_EDIT_WORK.code|escape:'html'}" required><small>前台详情页和客服咨询都会显示这个编号。</small></label>
              <label class="gzca-field"><span>所属分类</span><select name="album_id" required>{foreach from=$GZCA_CATEGORY_OPTION_GROUPS item=group}<optgroup label="{$group.name|escape:'html'}">{foreach from=$group.options item=category}<option value="{$category.id}" {if $category.id eq $GZCA_EDIT_WORK.category_id}selected{/if}>{$category.option_label|escape:'html'} · {$category.prefix|escape:'html'}</option>{/foreach}</optgroup>{/foreach}</select></label>
              <label class="gzca-field"><span>上架状态</span><select name="status"><option value="online" {if $GZCA_EDIT_WORK.status eq 'online'}selected{/if}>已上架</option><option value="offline" {if $GZCA_EDIT_WORK.status eq 'offline'}selected{/if}>已下架</option></select><small>控制是否进入前台作品流。</small></label>
              <label class="gzca-field"><span>公开状态</span><select name="visibility"><option value="public" {if $GZCA_EDIT_WORK.is_public}selected{/if}>公开</option><option value="private" {if $GZCA_EDIT_WORK.is_public}{else}selected{/if}>私密</option></select><small>前台只显示“已上架 + 公开”的作品。</small></label>
              <label class="gzca-field gzca-field-wide"><span>作品说明</span><textarea name="description" rows="4">{$GZCA_EDIT_WORK.description|escape:'html'}</textarea></label>
              <label class="gzca-field"><span>排序数字</span><input type="number" name="sort_order" value="{$GZCA_EDIT_WORK.sort_order}" min="0"><small>数字越小越靠前。</small></label>
              <label class="gzca-field"><span>下载量</span><input type="number" name="download_count" value="{$GZCA_EDIT_WORK.download_count}" min="0"><small>用于首页“下载量高”排序，可按实际数据调整。</small></label>
              <label class="gzca-field"><span>浏览量</span><input type="number" value="{$GZCA_EDIT_WORK.hit}" readonly><small>Piwigo 自动累计，用于首页“热门作品”排序。</small></label>
              <label class="gzca-check"><input type="checkbox" name="featured" value="1" {if $GZCA_EDIT_WORK.featured}checked{/if}><span><strong>加入精选作品</strong><small>勾选后会优先出现在首页“精美作品”。</small></span></label>
              <label class="gzca-check"><input type="checkbox" name="set_cover" value="1"><span><strong>同时设为分类封面</strong></span></label>
              <div class="gzca-form-actions"><button class="gzca-button gzca-button-primary" type="submit">保存修改</button><a class="gzca-button gzca-button-quiet" href="{$GZCA_URLS.works|escape:'html'}">取消</a></div>
            </div>
          </form>
        </section>
      {/if}

      <section class="gzca-panel gzca-works-panel">
        <form class="gzca-filterbar gzca-work-filterbar" action="{$ROOT_URL}admin.php" method="get" data-work-filter-form>
          <input type="hidden" name="page" value="plugin-GuozhanClientAdmin"><input type="hidden" name="tab" value="works">
          <label><span class="gzca-visually-hidden">搜索作品</span><input type="search" name="q" value="{$GZCA_FILTERS.q|escape:'html'}" placeholder="搜索名称、编号或文件名"></label>
          <select name="album_id" aria-label="筛选分类" data-auto-filter><option value="0">全部分类</option>{foreach from=$GZCA_CATEGORY_OPTION_GROUPS item=group}<optgroup label="{$group.name|escape:'html'}">{foreach from=$group.options item=category}<option value="{$category.id}" {if $category.id eq $GZCA_FILTERS.album_id}selected{/if}>{$category.option_label|escape:'html'} · {$category.prefix|escape:'html'}</option>{/foreach}</optgroup>{/foreach}</select>
          <select name="status" aria-label="筛选上架状态" data-auto-filter><option value="">全部上架状态</option><option value="online" {if $GZCA_FILTERS.status eq 'online'}selected{/if}>已上架</option><option value="offline" {if $GZCA_FILTERS.status eq 'offline'}selected{/if}>已下架</option></select>
          <select name="visibility" aria-label="筛选公开状态" data-auto-filter><option value="">全部公开状态</option><option value="public" {if $GZCA_FILTERS.visibility eq 'public'}selected{/if}>公开</option><option value="private" {if $GZCA_FILTERS.visibility eq 'private'}selected{/if}>私密</option></select>
          <select name="per_page" aria-label="每页显示数量" data-auto-filter>{foreach from=$GZCA_PAGER.per_page_options item=per_page}<option value="{$per_page}" {if $GZCA_PAGER.per_page eq $per_page}selected{/if}>每页 {$per_page} 张</option>{/foreach}</select>
          <button class="gzca-button gzca-button-primary" type="submit">搜索</button>
        </form>

        <form id="gzca-bulk-form" class="gzca-bulkbar" action="{$GZCA_URLS.works|escape:'html'}" method="post" data-bulk-form>
          <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="bulk_works"><input type="hidden" name="bulk_delete_confirm" value="">
          <label class="gzca-check-inline"><input type="checkbox" data-bulk-select-all><span>本页全选</span></label>
          <span class="gzca-bulk-count" data-bulk-count>已选 0 张</span>
          <select name="bulk_action" required data-bulk-action><option value="">批量操作</option><option value="online">批量上架</option><option value="offline">批量下架</option><option value="move">批量移动到板块</option><option value="delete">批量永久删除作品</option></select>
          <select name="bulk_album_id" aria-label="批量移动目标板块"><option value="0">选择移动目标板块</option>{foreach from=$GZCA_CATEGORY_OPTION_GROUPS item=group}<optgroup label="{$group.name|escape:'html'}">{foreach from=$group.options item=category}<option value="{$category.id}">{$category.option_label|escape:'html'} · {$category.prefix|escape:'html'}</option>{/foreach}</optgroup>{/foreach}</select>
          <button class="gzca-button gzca-button-primary" type="submit">执行</button>
          <small class="gzca-bulk-help"><strong>当前筛选 {$GZCA_PAGER.total} 张 · 原图约 {$GZCA_PAGER.total_size_label|escape:'html'}</strong><span>批量移动只调整所属板块，不自动修改作品编号；批量永久删除会清理服务器图片文件和数据库记录。</span></small>
        </form>

        <div class="gzca-table-wrap">
          <table class="gzca-table gzca-work-table">
            <thead><tr><th class="gzca-select-col"><input type="checkbox" aria-label="全选本页作品" data-bulk-select-all></th><th>作品</th><th>编号</th><th>分类</th><th>上架</th><th>公开</th><th>前台</th><th>操作</th></tr></thead>
            <tbody>
            {foreach from=$GZCA_WORKS item=work}
              <tr>
                <td class="gzca-select-col"><input type="checkbox" name="selected_images[]" value="{$work.id}" form="gzca-bulk-form" aria-label="选择 {$work.display_name|escape:'html'}" data-bulk-item></td>
                <td><div class="gzca-work-cell"><img src="{$work.thumb_url|escape:'html'}" alt=""><span><strong>{$work.display_name|escape:'html'}</strong><small>图片 ID {$work.id} · 原图 {$work.filesize_label|escape:'html'}</small></span></div></td>
                <td><code>{$work.code|escape:'html'}</code></td>
                <td><div class="gzca-category-cell" title="{$work.category_display_name|escape:'html'}"><strong>{$work.category_board_name|escape:'html'}</strong><small>{$work.category_leaf_name|escape:'html'}</small></div></td>
                <td><span class="gzca-status {if $work.status eq 'online'}is-online{else}is-offline{/if}">{if $work.status eq 'online'}已上架{else}已下架{/if}</span></td>
                <td><span class="gzca-status {if $work.is_public}is-online{else}is-offline{/if}">{if $work.is_public}公开{else}私密{/if}</span></td>
                <td><span class="gzca-status {if $work.frontend_visible}is-online{else}is-offline{/if}">{if $work.frontend_visible}显示{else}不显示{/if}</span></td>
                <td><div class="gzca-row-actions"><a href="{$GZCA_URLS.works|escape:'html'}&amp;edit={$work.id}#edit-work">编辑</a><form method="post" action="{$GZCA_URLS.works|escape:'html'}"><input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="toggle_work"><input type="hidden" name="image_id" value="{$work.id}"><button type="submit">{if $work.status eq 'online'}下架{else}上架{/if}</button></form>{if $work.category_id}<form method="post" action="{$GZCA_URLS.works|escape:'html'}"><input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="set_cover"><input type="hidden" name="image_id" value="{$work.id}"><input type="hidden" name="album_id" value="{$work.category_id}"><button type="submit">设为封面</button></form>{/if}<form method="post" action="{$GZCA_URLS.works|escape:'html'}"><input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="download_work"><input type="hidden" name="image_id" value="{$work.id}"><button class="gzca-row-download" type="submit">下载原图</button></form><form method="post" action="{$GZCA_URLS.works|escape:'html'}" data-work-name="{$work.display_name|escape:'html'}"><input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="delete_work"><input type="hidden" name="image_id" value="{$work.id}"><input type="hidden" name="delete_confirm" value=""><button class="gzca-row-delete" type="submit">删除</button></form></div></td>
              </tr>
            {foreachelse}
              <tr><td colspan="8"><div class="gzca-empty"><strong>没有找到作品</strong><span>调整搜索条件，或者上传新的作品。</span></div></td></tr>
            {/foreach}
            </tbody>
          </table>
        </div>

        <nav class="gzca-pager gzca-work-pager" aria-label="作品分页">
          <span>共 {$GZCA_PAGER.total} 张 · 每页 {$GZCA_PAGER.per_page} 张 · 第 {$GZCA_PAGER.page}/{$GZCA_PAGER.pages} 页</span>
          <div class="gzca-page-links">
            {if $GZCA_PAGER.first_url}<a class="gzca-page-link is-nav" href="{$GZCA_PAGER.first_url|escape:'html'}">首页</a>{/if}
            {if $GZCA_PAGER.previous_url}<a class="gzca-page-link is-nav" href="{$GZCA_PAGER.previous_url|escape:'html'}">上一页</a>{else}<span class="gzca-page-link is-nav is-disabled" aria-disabled="true">上一页</span>{/if}
            {foreach from=$GZCA_PAGER.links item=link}{if $link.page eq 0}<span class="gzca-page-ellipsis">...</span>{elseif $link.current}<span class="gzca-page-link is-current" aria-current="page">{$link.label|escape:'html'}</span>{else}<a class="gzca-page-link" href="{$link.url|escape:'html'}">{$link.label|escape:'html'}</a>{/if}{/foreach}
            {if $GZCA_PAGER.next_url}<a class="gzca-page-link is-nav" href="{$GZCA_PAGER.next_url|escape:'html'}">下一页</a>{else}<span class="gzca-page-link is-nav is-disabled" aria-disabled="true">下一页</span>{/if}
            {if $GZCA_PAGER.last_url}<a class="gzca-page-link is-nav" href="{$GZCA_PAGER.last_url|escape:'html'}">末页</a>{/if}
          </div>
          <form class="gzca-page-jump" action="{$ROOT_URL}admin.php" method="get">
            <input type="hidden" name="page" value="plugin-GuozhanClientAdmin"><input type="hidden" name="tab" value="works"><input type="hidden" name="q" value="{$GZCA_FILTERS.q|escape:'html'}"><input type="hidden" name="album_id" value="{$GZCA_FILTERS.album_id}"><input type="hidden" name="status" value="{$GZCA_FILTERS.status|escape:'html'}"><input type="hidden" name="visibility" value="{$GZCA_FILTERS.visibility|escape:'html'}"><input type="hidden" name="per_page" value="{$GZCA_FILTERS.per_page}">
            <label><span>跳到</span><input type="number" name="p" min="1" max="{$GZCA_PAGER.pages}" value="{$GZCA_PAGER.page}"></label><button class="gzca-button gzca-button-quiet" type="submit">跳转</button>
          </form>
        </nav>
      </section>
    {/if}

    {if $GZCA_TAB eq 'categories'}
      <div class="gzca-category-layout">
        <section class="gzca-panel gzca-category-form-panel">
          <div class="gzca-panel-head"><div><p>{if $GZCA_EDIT_CATEGORY}编辑分类{else}新增分类{/if}</p><h3>{if $GZCA_EDIT_CATEGORY}{$GZCA_EDIT_CATEGORY.name|escape:'html'}{else}建立相册层级{/if}</h3></div>{if $GZCA_EDIT_CATEGORY}<a class="gzca-text-link" href="{$GZCA_URLS.categories|escape:'html'}">取消编辑</a>{/if}</div>
          <form class="gzca-stack-form" action="{if $GZCA_EDIT_CATEGORY}{$GZCA_URLS.categories|escape:'html'}&amp;edit={$GZCA_EDIT_CATEGORY.id}{else}{$GZCA_URLS.categories|escape:'html'}{/if}" method="post" data-category-form>
            <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="{if $GZCA_EDIT_CATEGORY}save_category{else}create_category{/if}">{if $GZCA_EDIT_CATEGORY}<input type="hidden" name="category_id" value="{$GZCA_EDIT_CATEGORY.id}">{/if}
            <label class="gzca-field"><span>分类名称</span><input type="text" name="name" value="{if $GZCA_EDIT_CATEGORY}{$GZCA_EDIT_CATEGORY.name|escape:'html'}{/if}" placeholder="例如 油画系列 / 风景画" required></label>
            {if !$GZCA_EDIT_CATEGORY}<label class="gzca-field"><span>上级板块</span><select name="parent_id" data-category-parent><option value="" data-kind="catalog">无，创建大板块</option>{foreach from=$GZCA_PARENT_CATEGORIES item=category}<option value="{$category.id}" data-kind="{$category.category_kind|escape:'html'}">{$category.name|escape:'html'}</option>{/foreach}</select><small>新增画展时请选择“中美协展览专项画稿”。</small></label>{/if}
            <label class="gzca-field"><span>板块类型</span><select name="category_kind" data-category-kind><option value="catalog" {if !$GZCA_EDIT_CATEGORY or $GZCA_EDIT_CATEGORY.category_kind neq 'exhibition'}selected{/if}>作品分类</option><option value="exhibition" {if $GZCA_EDIT_CATEGORY and $GZCA_EDIT_CATEGORY.category_kind eq 'exhibition'}selected{/if}>专项画展</option></select><small>用于后台分组和前台识别，不影响现有作品。</small></label>
            <label class="gzca-field"><span>编号前缀</span><input type="text" name="code_prefix" value="{if $GZCA_EDIT_CATEGORY}{$GZCA_EDIT_CATEGORY.code_prefix|escape:'html'}{/if}" placeholder="例如 YH-FJ" required><small>上传作品时会用它自动生成连续编号。</small></label>
            <label class="gzca-field"><span>分类说明</span><textarea name="description" rows="4" placeholder="简要说明这个分类展示什么内容">{if $GZCA_EDIT_CATEGORY}{$GZCA_EDIT_CATEGORY.description|escape:'html'}{/if}</textarea></label>
            {if $GZCA_EDIT_CATEGORY}<label class="gzca-field"><span>排序数字</span><input type="number" name="sort_order" value="{$GZCA_EDIT_CATEGORY.custom_sort_order}" min="0"><small>数字越小越靠前。</small></label>{/if}
            <label class="gzca-check"><input type="checkbox" name="visible" value="1" {if !$GZCA_EDIT_CATEGORY or $GZCA_EDIT_CATEGORY.visible eq 'true'}checked{/if}><span><strong>在前台显示这个分类</strong><small>取消后分类会暂时隐藏，但图片不会删除。</small></span></label>
            <label class="gzca-check"><input type="checkbox" name="direct_upload" value="1" {if !$GZCA_EDIT_CATEGORY or $GZCA_EDIT_CATEGORY.direct_upload}checked{/if}><span><strong>允许作品直接放在这个板块</strong><small>前台可见板块默认都应允许上传；有子分类的大板块也可以直接承接作品。</small></span></label>
            <label class="gzca-check"><input type="checkbox" name="reserved" value="1" {if $GZCA_EDIT_CATEGORY and $GZCA_EDIT_CATEGORY.reserved}checked{/if}><span><strong>标记为预留画展</strong><small>没有图片也会保留入口；确定正式展名后可取消勾选。</small></span></label>
            <button class="gzca-button gzca-button-primary gzca-button-block" type="submit">{if $GZCA_EDIT_CATEGORY}保存分类{else}创建分类{/if}</button>
          </form>
          {if $GZCA_EDIT_CATEGORY}
            <div class="gzca-danger-zone {if $GZCA_EDIT_CATEGORY.is_hidden}is-restore{/if}">
              <strong>{if $GZCA_EDIT_CATEGORY.is_hidden}显示板块{else}隐藏板块{/if}</strong>
              <p>{$GZCA_EDIT_CATEGORY.hide_note|escape:'html'}</p>
              <form method="post" action="{$GZCA_URLS.categories|escape:'html'}" data-category-name="{$GZCA_EDIT_CATEGORY.name|escape:'html'}">
                <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="{if $GZCA_EDIT_CATEGORY.is_hidden}show_category{else}hide_category{/if}"><input type="hidden" name="category_id" value="{$GZCA_EDIT_CATEGORY.id}">
                <button class="gzca-button {if $GZCA_EDIT_CATEGORY.is_hidden}gzca-button-restore{else}gzca-button-warn{/if} gzca-button-block" type="submit">{if $GZCA_EDIT_CATEGORY.is_hidden}显示这个板块{else}隐藏这个板块{/if}</button>
              </form>
            </div>
            <div class="gzca-danger-zone is-permanent">
              <strong>永久删除板块</strong>
              <p>{$GZCA_EDIT_CATEGORY.delete_note|escape:'html'}此操作不可恢复；如只是暂时不展示，请使用上面的“隐藏”。</p>
              <form method="post" action="{$GZCA_URLS.categories|escape:'html'}" data-category-name="{$GZCA_EDIT_CATEGORY.name|escape:'html'}">
                <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="delete_category"><input type="hidden" name="category_id" value="{$GZCA_EDIT_CATEGORY.id}"><input type="hidden" name="delete_confirm" value="">
                <button class="gzca-button gzca-button-danger gzca-button-block" type="submit">永久删除这个板块</button>
              </form>
            </div>
          {/if}
        </section>

        <section class="gzca-panel gzca-category-list-panel" data-category-browser>
          <div class="gzca-panel-head"><div><p>前台板块</p><h3>分类与专项画展</h3></div><span class="gzca-help">{$GZCA_STATS.categories} 个大板块</span></div>
          <div class="gzca-category-toolbar">
            <label class="gzca-field gzca-category-filter-field"><span>按分类选择</span><select data-category-filter><option value="all">全部板块</option><option value="kind-catalog">作品分类</option><option value="kind-exhibition">专项画展</option><option value="hidden">已隐藏板块</option>{foreach from=$GZCA_CATEGORY_GROUPS item=group}<option value="group-{$group.id}">{$group.name|escape:'html'}</option>{/foreach}</select></label>
            <label class="gzca-field gzca-category-filter-field"><span>每页显示</span><select data-category-per-page><option value="4" selected>每页 4 组</option><option value="6">每页 6 组</option><option value="10">每页 10 组</option></select></label>
          </div>
          <nav class="gzca-category-pager" aria-label="分类分页">
            <span data-category-page-summary>正在整理板块</span>
            <div><button class="gzca-button gzca-button-quiet" type="button" data-category-prev>上一页</button><span data-category-page-label>第 1 / 1 页</span><button class="gzca-button gzca-button-quiet" type="button" data-category-next>下一页</button></div>
          </nav>
          <div class="gzca-category-groups" data-category-groups>
          {foreach from=$GZCA_CATEGORY_GROUPS item=group}
            <section class="gzca-category-group {if $group.category_kind eq 'exhibition'}is-exhibition{/if}" data-category-card data-category-id="{$group.id}" data-category-kind="{$group.category_kind|escape:'html'}" data-category-hidden="{if $group.is_hidden}1{else}0{/if}">
              <article class="gzca-category-parent">
                <div><span class="gzca-category-level">{if $group.category_kind eq 'exhibition'}展览总目录{elseif $group.child_count eq 0 and $group.direct_upload}直接分类{else}大分类{/if}</span><strong>{$group.name|escape:'html'}</strong><small>{$group.code_prefix|default:'未设置前缀'|escape:'html'} · {$group.image_count} 张作品 · {$group.child_count} 个子板块{if $group.direct_upload} · 可直接上传{/if}</small></div>
                <span class="gzca-status {if $group.is_hidden}is-offline{elseif $group.visible eq 'true'}is-online{else}is-offline{/if}">{if $group.is_hidden}已隐藏{elseif $group.visible eq 'true'}显示中{else}已隐藏{/if}</span>
                <div class="gzca-category-actions"><a class="gzca-button gzca-button-quiet" href="{$GZCA_URLS.categories|escape:'html'}&amp;edit={$group.id}">编辑</a><form method="post" action="{$GZCA_URLS.categories|escape:'html'}" data-category-name="{$group.name|escape:'html'}"><input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="{if $group.is_hidden}show_category{else}hide_category{/if}"><input type="hidden" name="category_id" value="{$group.id}"><button class="gzca-button {if $group.is_hidden}gzca-button-restore{else}gzca-button-warn{/if}" type="submit">{if $group.is_hidden}显示{else}隐藏{/if}</button></form><form method="post" action="{$GZCA_URLS.categories|escape:'html'}" data-category-name="{$group.name|escape:'html'}"><input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="delete_category"><input type="hidden" name="category_id" value="{$group.id}"><input type="hidden" name="delete_confirm" value=""><button class="gzca-button gzca-button-danger" type="submit">删除</button></form></div>
              </article>
              {if $group.child_count gt 0}<div class="gzca-category-children">
                {foreach from=$group.children item=category}<article>
                  <div><span class="gzca-category-level">{if $category.reserved}预留画展{elseif $category.category_kind eq 'exhibition'}专项画展{else}小分类{/if}</span><strong>{$category.name|escape:'html'}</strong><small>{$category.code_prefix|default:'未设置前缀'|escape:'html'} · {$category.image_count} 张作品{if $category.direct_upload} · 可直接上传{/if}</small></div>
                  <span class="gzca-status {if $category.is_hidden}is-offline{elseif $category.visible eq 'true'}is-online{else}is-offline{/if}">{if $category.is_hidden}已隐藏{elseif $category.visible eq 'true'}显示中{else}已隐藏{/if}</span>
                  <div class="gzca-category-actions"><a class="gzca-button gzca-button-quiet" href="{$GZCA_URLS.categories|escape:'html'}&amp;edit={$category.id}">编辑</a><form method="post" action="{$GZCA_URLS.categories|escape:'html'}" data-category-name="{$category.name|escape:'html'}"><input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="{if $category.is_hidden}show_category{else}hide_category{/if}"><input type="hidden" name="category_id" value="{$category.id}"><button class="gzca-button {if $category.is_hidden}gzca-button-restore{else}gzca-button-warn{/if}" type="submit">{if $category.is_hidden}显示{else}隐藏{/if}</button></form><form method="post" action="{$GZCA_URLS.categories|escape:'html'}" data-category-name="{$category.name|escape:'html'}"><input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="delete_category"><input type="hidden" name="category_id" value="{$category.id}"><input type="hidden" name="delete_confirm" value=""><button class="gzca-button gzca-button-danger" type="submit">删除</button></form></div>
                </article>{/foreach}
              </div>{/if}
            </section>
          {foreachelse}<div class="gzca-empty"><strong>还没有前台板块</strong><span>点击“建立前端对应分类”，或从左侧创建第一个大板块。</span></div>{/foreach}
          </div>
          <div class="gzca-empty gzca-category-filter-empty" data-category-empty hidden><strong>没有匹配的板块</strong><span>换一个分类筛选条件，或先创建新的板块。</span></div>
          <nav class="gzca-category-pager gzca-category-pager-bottom" aria-label="分类分页">
            <span data-category-page-summary>正在整理板块</span>
            <div><button class="gzca-button gzca-button-quiet" type="button" data-category-prev>上一页</button><span data-category-page-label>第 1 / 1 页</span><button class="gzca-button gzca-button-quiet" type="button" data-category-next>下一页</button></div>
          </nav>
        </section>
      </div>
    {/if}

    {if $GZCA_TAB eq 'security'}
      <div class="gzca-account-page">
        <section class="gzca-account-hero" aria-label="管理员账号资料">
          <span class="gzca-account-avatar">管</span>
          <div class="gzca-account-copy">
            <span>管理员账号</span>
            <h3>{$GZCA_ADMIN_IDENTITY.username|escape:'html'}</h3>
            <div class="gzca-account-meta">
              <span>{$GZCA_ADMIN_IDENTITY.role|escape:'html'}</span>
              <span>登录有效至 {$GZCA_ADMIN_IDENTITY.expires_at|escape:'html'}</span>
              <span>{if $GZCA_EMAIL_SECURITY.verified}{$GZCA_EMAIL_SECURITY.masked_email|escape:'html'}{else}恢复邮箱未绑定{/if}</span>
            </div>
          </div>
          <div class="gzca-account-actions">
            <span class="gzca-security-badge is-verified">当前设备已登录</span>
            <a class="gzca-button gzca-button-danger" href="{$GZCA_URLS.logout|escape:'html'}">退出当前账号</a>
          </div>
        </section>

        <div class="gzca-security-layout">
          <section id="recovery-email" class="gzca-panel gzca-security-panel">
            <div class="gzca-panel-head">
              <div><p>账号找回</p><h3>恢复邮箱</h3></div>
              <span class="gzca-security-badge {if $GZCA_EMAIL_SECURITY.verified}is-verified{else}is-unverified{/if}">{if $GZCA_EMAIL_SECURITY.verified}已验证{else}待绑定{/if}</span>
            </div>

            <div class="gzca-security-overview">
              <div class="gzca-security-status">
                <span>当前状态</span>
                {if $GZCA_EMAIL_SECURITY.verified}
                  <strong>{$GZCA_EMAIL_SECURITY.masked_email|escape:'html'}</strong>
                  <small>验证时间 {$GZCA_EMAIL_SECURITY.verified_at|escape:'html'}，忘记密码时验证码会发送到此邮箱。</small>
                {else}
                  <strong>尚未绑定恢复邮箱</strong>
                  <small>请由最终客户绑定自己的邮箱，交付方邮箱不会作为默认恢复邮箱。</small>
                {/if}
              </div>

              {if !$GZCA_EMAIL_SECURITY.storage_ready}
                <div class="gzca-security-alert is-danger"><strong>账号安全数据表尚未就绪</strong><span>请先完成插件升级，当前不会写入或替换邮箱。</span></div>
              {elseif !$GZCA_SECURITY_MAIL.ready}
                <div class="gzca-security-alert is-warning"><strong>暂时不能发送验证码</strong><span>{$GZCA_SECURITY_MAIL.message|escape:'html'}</span></div>
              {elseif $GZCA_EMAIL_SECURITY.requires_binding}
                <div class="gzca-security-alert is-info"><strong>请绑定客户邮箱</strong><span>验证成功后即可用于找回密码和高风险操作确认。</span></div>
              {/if}
            </div>

            {if $GZCA_EMAIL_SECURITY.storage_ready and $GZCA_SECURITY_MAIL.ready}
              <div class="gzca-security-section">
                <div class="gzca-security-section-head">
                  <strong>{if $GZCA_EMAIL_SECURITY.verified}更换恢复邮箱{else}绑定恢复邮箱{/if}</strong>
                  <span>需要当前密码和新邮箱验证码</span>
                </div>
                <div class="gzca-security-steps">
                  <section class="gzca-security-step">
                    <div class="gzca-security-step-head"><b>1</b><strong>发送验证码</strong></div>
                    <form class="gzca-security-form" action="{$GZCA_URLS.security|escape:'html'}" method="post">
                      <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}">
                      <input type="hidden" name="gzca_action" value="send_bind_email_code">
                      <label class="gzca-field"><span>客户的新邮箱</span><input type="email" name="new_email" value="{$GZCA_SECURITY_FORM.new_email|escape:'html'}" maxlength="255" autocomplete="email" required><small>验证成功前不会替换当前账号资料。</small></label>
                      <label class="gzca-field"><span>当前管理员密码</span><input type="password" name="current_password" maxlength="256" autocomplete="current-password" required></label>
                      <button class="gzca-button gzca-button-quiet" type="submit">发送邮箱验证码</button>
                    </form>
                  </section>

                  <section class="gzca-security-step">
                    <div class="gzca-security-step-head"><b>2</b><strong>验证并绑定</strong></div>
                    <form class="gzca-security-form gzca-security-verify-form" action="{$GZCA_URLS.security|escape:'html'}" method="post">
                      <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}">
                      <input type="hidden" name="gzca_action" value="verify_bind_email">
                      <label class="gzca-field"><span>同一个新邮箱</span><input type="email" name="new_email" value="{$GZCA_SECURITY_FORM.new_email|escape:'html'}" maxlength="255" autocomplete="email" required></label>
                      <label class="gzca-field"><span>六位验证码</span><input type="text" name="verification_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required>{if $GZCA_EMAIL_SECURITY.bind_challenge.active}<small>验证码有效至 {$GZCA_EMAIL_SECURITY.bind_challenge.expires_at|escape:'html'}。</small>{/if}</label>
                      <label class="gzca-field"><span>再次输入当前密码</span><input type="password" name="current_password" maxlength="256" autocomplete="current-password" required></label>
                      <button class="gzca-button gzca-button-primary" type="submit">验证并绑定邮箱</button>
                    </form>
                  </section>
                </div>
              </div>
            {/if}
          </section>

          <section id="login-devices" class="gzca-panel gzca-security-panel">
            <div class="gzca-panel-head">
              <div><p>登录保护</p><h3>登录设备</h3></div>
              <span class="gzca-security-badge is-verified">当前设备保留</span>
            </div>
            <div class="gzca-security-device-row">
              <div class="gzca-security-device-copy">
                <strong>退出其他设备</strong>
                <span>撤销其他浏览器的会话和记住登录，当前浏览器继续保持登录。</span>
              </div>
              <form class="gzca-security-device-form" action="{$GZCA_URLS.security|escape:'html'}#login-devices" method="post">
                <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}">
                <input type="hidden" name="gzca_action" value="revoke_other_sessions">
                <label class="gzca-field"><span>当前管理员密码</span><input type="password" name="current_password" maxlength="256" autocomplete="current-password" required></label>
                <label class="gzca-check gzca-security-device-confirm"><input type="checkbox" name="confirm_other_sessions" value="1" required><span><strong>确认退出其他设备</strong><small>其他设备需要重新输入账号和密码。</small></span></label>
                <button class="gzca-button gzca-button-danger" type="submit">退出其他设备</button>
              </form>
            </div>
          </section>

          <section class="gzca-panel gzca-security-panel">
            <div class="gzca-panel-head">
              <div><p>高风险操作验证</p><h3>修改管理员密码</h3></div>
              <span class="gzca-security-badge {if $GZCA_EMAIL_SECURITY.verified}is-verified{else}is-unverified{/if}">{if $GZCA_EMAIL_SECURITY.verified}邮箱保护已开启{else}需先绑定邮箱{/if}</span>
            </div>

            {if $GZCA_EMAIL_SECURITY.verified}
              <div class="gzca-security-overview">
                <div class="gzca-security-status">
                  <span>验证码接收邮箱</span>
                  <strong>{$GZCA_EMAIL_SECURITY.masked_email|escape:'html'}</strong>
                  <small>修改成功后，旧登录、记住登录、重置链接和 API Key 都会失效。</small>
                </div>
              </div>
              <div class="gzca-security-section">
                <div class="gzca-security-steps">
                  <section class="gzca-security-step">
                    <div class="gzca-security-step-head"><b>1</b><strong>发送改密验证码</strong></div>
                    <form class="gzca-security-form" action="{$GZCA_URLS.security|escape:'html'}" method="post">
                      <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}">
                      <input type="hidden" name="gzca_action" value="send_password_code">
                      <label class="gzca-field"><span>当前管理员密码</span><input type="password" name="current_password" maxlength="256" autocomplete="current-password" required></label>
                      <button class="gzca-button gzca-button-quiet" type="submit">发送改密验证码</button>
                    </form>
                  </section>

                  <section class="gzca-security-step">
                    <div class="gzca-security-step-head"><b>2</b><strong>验证并修改密码</strong></div>
                    <form class="gzca-security-form" action="{$GZCA_URLS.security|escape:'html'}" method="post">
                      <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}">
                      <input type="hidden" name="gzca_action" value="change_admin_password">
                      <label class="gzca-field"><span>当前管理员密码</span><input type="password" name="current_password" maxlength="256" autocomplete="current-password" required></label>
                      <label class="gzca-field"><span>邮箱验证码</span><input type="text" name="verification_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required>{if $GZCA_EMAIL_SECURITY.password_challenge.active}<small>验证码有效至 {$GZCA_EMAIL_SECURITY.password_challenge.expires_at|escape:'html'}。</small>{/if}</label>
                      <label class="gzca-field"><span>新密码</span><input type="password" name="new_password" minlength="12" maxlength="128" autocomplete="new-password" required></label>
                      <label class="gzca-field"><span>确认新密码</span><input type="password" name="new_password_confirm" minlength="12" maxlength="128" autocomplete="new-password" required></label>
                      <button class="gzca-button gzca-button-danger" type="submit">验证并修改密码</button>
                    </form>
                  </section>
                </div>
              </div>
            {else}
              <div class="gzca-security-empty">
                <strong>请先绑定恢复邮箱</strong>
                <span>邮箱验证完成后，这里会开放管理员密码修改功能。</span>
              </div>
            {/if}
          </section>
        </div>
      </div>
    {/if}

    {if $GZCA_TAB eq 'contact'}
      <form class="gzca-contact-layout" action="{$GZCA_URLS.contact|escape:'html'}" method="post" enctype="multipart/form-data">
        <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="save_contact">
        <section class="gzca-panel">
          <div class="gzca-panel-head"><div><p>对外展示资料</p><h3>品牌与客服信息</h3></div><span class="gzca-help">保存后同步前台</span></div>
          <div class="gzca-contact-fields">
            <label class="gzca-field"><span>展示馆名称</span><input type="text" name="brand_name" value="{$GZCA_CONFIG.brand_name|escape:'html'}" required></label>
            <label class="gzca-field"><span>英文名称</span><input type="text" name="brand_en" value="{$GZCA_CONFIG.brand_en|escape:'html'}" placeholder="AI Graphics Learning Plan"></label>
            <label class="gzca-field"><span>联系电话</span><input type="text" name="phone" value="{$GZCA_CONFIG.phone|escape:'html'}" placeholder="可不填写"></label>
            <label class="gzca-check"><input type="checkbox" name="frontend_sync" value="1" {if $GZCA_CONFIG.frontend_sync}checked{/if}><span><strong>同步到网站客服区域</strong><small>会同步前台联系客服页、客服弹窗里的两个微信号、说明和二维码。</small></span></label>
          </div>

          <div class="gzca-contact-admin-grid">
            {foreach from=$GZCA_CONTACTS item=contact}
              <article class="gzca-contact-card">
                <div class="gzca-contact-card-head">
                  <div><p>客服 {$contact.slot}</p><h4>{$contact.label|escape:'html'}</h4></div>
                  <label class="gzca-switch"><input type="checkbox" name="contacts[{$contact.index}][enabled]" value="1" {if $contact.enabled}checked{/if} {if $contact.slot eq 1}disabled{/if}><span>{if $contact.slot eq 1}默认启用{else}启用{/if}</span></label>
                  {if $contact.slot eq 1}<input type="hidden" name="contacts[{$contact.index}][enabled]" value="1">{/if}
                </div>
                <div class="gzca-contact-card-body">
                  <div class="gzca-qr-preview gzca-contact-qr-preview">{if $contact.qr_url}<img src="{$contact.qr_url|escape:'html'}" alt="客服 {$contact.slot} 二维码">{else}<span>客服 {$contact.slot}<br>二维码</span>{/if}</div>
                  <div class="gzca-contact-card-fields">
                    <label class="gzca-field"><span>显示名称</span><input type="text" name="contacts[{$contact.index}][label]" value="{$contact.label|escape:'html'}" required></label>
                    <label class="gzca-field"><span>微信号</span><input type="text" name="contacts[{$contact.index}][wechat]" value="{$contact.wechat|escape:'html'}" {if $contact.slot eq 1}required{/if}></label>
                    <label class="gzca-field"><span>联系说明</span><textarea name="contacts[{$contact.index}][note]" rows="3">{$contact.note|escape:'html'}</textarea></label>
                    <label class="gzca-field"><span>更换客服 {$contact.slot} 二维码</span><input type="file" name="contact_qr_{$contact.slot}" accept="image/jpeg,image/png,image/webp"><small>支持 JPG、PNG、WebP，最大 2MB。</small></label>
                  </div>
                </div>
              </article>
            {/foreach}
          </div>
        </section>
        <aside class="gzca-panel gzca-qr-panel">
          <div class="gzca-panel-head"><div><p>网站标识</p><h3>公司 Logo</h3></div></div>
          <div class="gzca-qr-preview gzca-logo-preview">{if $GZCA_LOGO_URL}<img src="{$GZCA_LOGO_URL|escape:'html'}" alt="当前公司 Logo">{else}<span>图</span>{/if}</div>
          <label class="gzca-field"><span>更换 Logo</span><input type="file" name="brand_logo" accept="image/jpeg,image/png,image/webp"><small>建议透明 PNG 或 WebP，最大 4MB。</small></label>
          <div class="gzca-side-note"><strong>客服显示规则</strong><span>前台最多显示两个已启用客服。客服1作为默认客服保留，客服2开启后会在二维码区并列展示。</span></div>
          <button class="gzca-button gzca-button-primary gzca-button-block" type="submit">保存客服信息</button>
        </aside>
      </form>
    {/if}
  </main>
</div>
