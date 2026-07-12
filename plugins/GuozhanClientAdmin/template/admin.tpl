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
      <a href="{$GZCA_URLS.upload|escape:'html'}" class="{if $GZCA_TAB eq 'upload'}is-active{/if}"><span>传</span>上传作品</a>
      <a href="{$GZCA_URLS.works|escape:'html'}" class="{if $GZCA_TAB eq 'works'}is-active{/if}"><span>图</span>作品管理</a>
      <a href="{$GZCA_URLS.categories|escape:'html'}" class="{if $GZCA_TAB eq 'categories'}is-active{/if}"><span>类</span>分类管理</a>
      <a href="{$GZCA_URLS.competition|escape:'html'}" class="{if $GZCA_TAB eq 'competition'}is-active{/if}"><span>赛</span>比赛管理</a>
      <a href="{$GZCA_URLS.contact|escape:'html'}" class="{if $GZCA_TAB eq 'contact'}is-active{/if}"><span>牌</span>品牌与客服</a>
    </nav>

    <div class="gzca-sidebar-footer">
      <a href="{$GZCA_URLS.gallery|escape:'html'}" target="_blank" rel="noopener">查看网站前台</a>
      <small>插件版本 {$GZCA_VERSION}</small>
    </div>
  </aside>

  <main class="gzca-main">
    <header class="gzca-page-header">
      <div>
        {if $GZCA_TAB eq 'dashboard'}<p>内容总览</p><h2>工作台</h2>{/if}
        {if $GZCA_TAB eq 'upload'}<p>添加图库内容</p><h2>上传作品</h2>{/if}
        {if $GZCA_TAB eq 'works'}<p>编号、封面与上下架</p><h2>作品管理</h2>{/if}
        {if $GZCA_TAB eq 'categories'}<p>主分类与小分类</p><h2>分类管理</h2>{/if}
        {if $GZCA_TAB eq 'competition'}<p>比赛类型与作品入口</p><h2>比赛管理</h2>{/if}
        {if $GZCA_TAB eq 'contact'}<p>同步前台 Logo 与联系方式</p><h2>品牌与客服</h2>{/if}
      </div>
      <div class="gzca-header-actions">
        <a class="gzca-button gzca-button-quiet" href="{$GZCA_URLS.gallery|escape:'html'}" target="_blank" rel="noopener">预览网站</a>
        {if $GZCA_TAB neq 'upload'}<a class="gzca-button gzca-button-primary" href="{$GZCA_URLS.upload|escape:'html'}">上传作品</a>{/if}
      </div>
    </header>

    {if $GZCA_IS_WEBMASTER}
      <div class="gzca-role-note"><strong>定制后台</strong><span>当前后台入口已接管为国展定制页面，原生 Piwigo 后台不再对客户后台展示。</span></div>
    {/if}

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
        <article><span>比赛作品</span><strong>{$GZCA_STATS.competition}</strong><a href="{$GZCA_URLS.competition|escape:'html'}">管理比赛</a></article>
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
          <div class="{if $GZCA_DATABASE_HEALTH.competition_media_table}is-ok{else}is-warning{/if}"><span>比赛类型表</span><strong>{if $GZCA_DATABASE_HEALTH.competition_media_table}已创建{else}尚未创建{/if}</strong></div>
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
        <div><b>2</b><span><strong>上传并关联比赛</strong><small>选择作品分类后批量上传，可同时加入水墨、油画、版画或水彩比赛。</small></span></div>
        <div><b>3</b><span><strong>检查并上架</strong><small>确认标题、编号、封面和比赛归属后再展示到网站。</small></span></div>
      </section>
    {/if}

    {if $GZCA_TAB eq 'upload'}
      <form class="gzca-form-layout" action="{$GZCA_URLS.upload|escape:'html'}" method="post" enctype="multipart/form-data" data-upload-form>
        <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}">
        <input type="hidden" name="gzca_action" value="upload_works">

        <section class="gzca-panel gzca-upload-panel">
          <div class="gzca-panel-head"><div><p>第一步</p><h3>选择作品图片</h3></div><span class="gzca-help">每次最多 20 张</span></div>
          <label class="gzca-dropzone" data-dropzone>
            <input type="file" name="artworks[]" accept="image/jpeg,image/png,image/webp" multiple required data-file-input>
            <span class="gzca-drop-icon">+</span>
            <strong>点击选择，或把图片拖到这里</strong>
            <small>支持 JPG、PNG、WebP；原图仅供后台留存，网站列表使用约 432px 缩略图，进入详情后才加载约 1224px 展示图。</small>
          </label>
          <div class="gzca-file-list" data-file-list aria-live="polite"></div>
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
            <small>前端出现的 10 个大板块、29 个子板块和预留画展都可以作为上传位置。</small>
          </label>
          <div class="gzca-upload-selection" data-upload-selection hidden><span>本批作品将上传到</span><strong data-upload-path></strong></div>
          <label class="gzca-field"><span>编号前缀</span><input type="text" name="code_prefix" placeholder="选择分类后自动填写" maxlength="48" data-code-prefix readonly><small>前缀跟随分类，系统自动生成连续编号，避免作品放错目录。</small></label>
          <label class="gzca-field"><span>比赛展示</span><select name="competition_medium_id"><option value="0">不进入比赛</option>{foreach from=$GZCA_ACTIVE_COMPETITION_MEDIA item=medium}<option value="{$medium.id}">{$medium.name|escape:'html'}</option>{/foreach}</select><small>同一作品仍保留普通分类和原作品编号。</small></label>
          <label class="gzca-check"><input type="checkbox" name="publish_now" value="1"><span><strong>上传后立即上架</strong><small>未勾选时先保存为下架状态，检查后再发布。</small></span></label>
          <label class="gzca-check"><input type="checkbox" name="set_cover" value="1"><span><strong>把第一张设为分类封面</strong><small>以后也可以在作品管理里重新设置。</small></span></label>
          <button class="gzca-button gzca-button-primary gzca-button-block" type="submit">开始上传作品</button>
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
              <label class="gzca-field"><span>作品名称</span><input type="text" name="title" value="{$GZCA_EDIT_WORK.display_name|escape:'html'}" required></label>
              <label class="gzca-field"><span>作品编号</span><input type="text" name="code" value="{$GZCA_EDIT_WORK.code|escape:'html'}" required><small>前台详情页和客服咨询都会显示这个编号。</small></label>
              <label class="gzca-field"><span>所属分类</span><select name="album_id" required>{foreach from=$GZCA_CATEGORY_OPTION_GROUPS item=group}<optgroup label="{$group.name|escape:'html'}">{foreach from=$group.options item=category}<option value="{$category.id}" {if $category.id eq $GZCA_EDIT_WORK.category_id}selected{/if}>{$category.option_label|escape:'html'} · {$category.prefix|escape:'html'}</option>{/foreach}</optgroup>{/foreach}</select></label>
              <label class="gzca-field"><span>上架状态</span><select name="status"><option value="online" {if $GZCA_EDIT_WORK.status eq 'online'}selected{/if}>已上架</option><option value="offline" {if $GZCA_EDIT_WORK.status eq 'offline'}selected{/if}>已下架</option></select></label>
              <label class="gzca-field gzca-field-wide"><span>作品说明</span><textarea name="description" rows="4">{$GZCA_EDIT_WORK.description|escape:'html'}</textarea></label>
              <label class="gzca-field"><span>排序数字</span><input type="number" name="sort_order" value="{$GZCA_EDIT_WORK.sort_order}" min="0"><small>数字越小越靠前。</small></label>
              <label class="gzca-field"><span>下载量</span><input type="number" name="download_count" value="{$GZCA_EDIT_WORK.download_count}" min="0"><small>用于首页“下载量高”排序，可按实际数据调整。</small></label>
              <label class="gzca-field"><span>比赛类型</span><select name="competition_medium_id"><option value="0">不进入比赛</option>{foreach from=$GZCA_COMPETITION_MEDIA item=medium}<option value="{$medium.id}" {if $medium.id eq $GZCA_EDIT_WORK.competition_medium_id}selected{/if}>{$medium.name|escape:'html'}{if $medium.status eq 'inactive'}（已停用）{/if}</option>{/foreach}</select></label>
              <label class="gzca-field"><span>比赛排序</span><input type="number" name="competition_sort_order" value="{$GZCA_EDIT_WORK.competition_sort_order}" min="0"><small>数字越小，在比赛页面越靠前。</small></label>
              <label class="gzca-field"><span>浏览量</span><input type="number" value="{$GZCA_EDIT_WORK.hit}" readonly><small>Piwigo 自动累计，用于首页“热门作品”排序。</small></label>
              <label class="gzca-check"><input type="checkbox" name="featured" value="1" {if $GZCA_EDIT_WORK.featured}checked{/if}><span><strong>加入精选作品</strong><small>勾选后会优先出现在首页“精美作品”。</small></span></label>
              <label class="gzca-check"><input type="checkbox" name="set_cover" value="1"><span><strong>同时设为分类封面</strong></span></label>
              <div class="gzca-form-actions"><button class="gzca-button gzca-button-primary" type="submit">保存修改</button><a class="gzca-button gzca-button-quiet" href="{$GZCA_URLS.works|escape:'html'}">取消</a></div>
            </div>
          </form>
        </section>
      {/if}

      <section class="gzca-panel">
        <form class="gzca-filterbar" action="{$ROOT_URL}admin.php" method="get" data-work-filter-form>
          <input type="hidden" name="page" value="plugin-GuozhanClientAdmin"><input type="hidden" name="tab" value="works">
          <label><span class="gzca-visually-hidden">搜索作品</span><input type="search" name="q" value="{$GZCA_FILTERS.q|escape:'html'}" placeholder="搜索名称、编号或文件名"></label>
          <select name="album_id" aria-label="筛选分类" data-auto-filter><option value="0">全部分类</option>{foreach from=$GZCA_CATEGORY_OPTION_GROUPS item=group}<optgroup label="{$group.name|escape:'html'}">{foreach from=$group.options item=category}<option value="{$category.id}" {if $category.id eq $GZCA_FILTERS.album_id}selected{/if}>{$category.option_label|escape:'html'} · {$category.prefix|escape:'html'}</option>{/foreach}</optgroup>{/foreach}</select>
          <select name="status" aria-label="筛选状态" data-auto-filter><option value="">全部状态</option><option value="online" {if $GZCA_FILTERS.status eq 'online'}selected{/if}>已上架</option><option value="offline" {if $GZCA_FILTERS.status eq 'offline'}selected{/if}>已下架</option></select>
          <select name="competition_medium_id" aria-label="筛选比赛类型" data-auto-filter><option value="0">全部比赛状态</option>{foreach from=$GZCA_COMPETITION_MEDIA item=medium}<option value="{$medium.id}" {if $medium.id eq $GZCA_FILTERS.competition_medium_id}selected{/if}>{$medium.name|escape:'html'}</option>{/foreach}</select>
          <button class="gzca-button gzca-button-primary" type="submit">搜索</button>
        </form>

        <div class="gzca-table-wrap">
          <table class="gzca-table">
            <thead><tr><th>作品</th><th>编号</th><th>分类</th><th>比赛</th><th>状态</th><th>操作</th></tr></thead>
            <tbody>
            {foreach from=$GZCA_WORKS item=work}
              <tr>
                <td><div class="gzca-work-cell"><img src="{$work.thumb_url|escape:'html'}" alt=""><span><strong>{$work.display_name|escape:'html'}</strong><small>图片 ID {$work.id}</small></span></div></td>
                <td><code>{$work.code|escape:'html'}</code></td>
                <td>{$work.category_name|escape:'html'}</td>
                <td>{if $work.in_competition}<span class="gzca-status is-competition">{$work.competition_name|escape:'html'}</span>{else}<span class="gzca-muted-label">未加入</span>{/if}</td>
                <td><span class="gzca-status {if $work.status eq 'online'}is-online{else}is-offline{/if}">{if $work.status eq 'online'}已上架{else}已下架{/if}</span></td>
                <td><div class="gzca-row-actions"><a href="{$GZCA_URLS.works|escape:'html'}&amp;edit={$work.id}#edit-work">编辑</a><form method="post" action="{$GZCA_URLS.works|escape:'html'}"><input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="toggle_work"><input type="hidden" name="image_id" value="{$work.id}"><button type="submit">{if $work.status eq 'online'}下架{else}上架{/if}</button></form>{if $work.category_id}<form method="post" action="{$GZCA_URLS.works|escape:'html'}"><input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="set_cover"><input type="hidden" name="image_id" value="{$work.id}"><input type="hidden" name="album_id" value="{$work.category_id}"><button type="submit">设为封面</button></form>{/if}</div></td>
              </tr>
            {foreachelse}
              <tr><td colspan="6"><div class="gzca-empty"><strong>没有找到作品</strong><span>调整搜索条件，或者上传新的作品。</span></div></td></tr>
            {/foreach}
            </tbody>
          </table>
        </div>

        <nav class="gzca-pager" aria-label="作品分页"><span>共 {$GZCA_PAGER.total} 张 · 第 {$GZCA_PAGER.page}/{$GZCA_PAGER.pages} 页</span><div>{if $GZCA_PAGER.previous_url}<a class="gzca-button gzca-button-quiet" href="{$GZCA_PAGER.previous_url|escape:'html'}">上一页</a>{/if}{if $GZCA_PAGER.next_url}<a class="gzca-button gzca-button-primary" href="{$GZCA_PAGER.next_url|escape:'html'}">下一页</a>{/if}</div></nav>
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
        </section>

        <section class="gzca-panel">
          <div class="gzca-panel-head"><div><p>前台板块</p><h3>分类与专项画展</h3></div><span class="gzca-help">{$GZCA_STATS.categories} 个大板块</span></div>
          <div class="gzca-category-groups">
          {foreach from=$GZCA_CATEGORY_GROUPS item=group}
            <section class="gzca-category-group {if $group.category_kind eq 'exhibition'}is-exhibition{/if}">
              <article class="gzca-category-parent">
                <div><span class="gzca-category-level">{if $group.category_kind eq 'exhibition'}展览总目录{elseif $group.child_count eq 0 and $group.direct_upload}直接分类{else}大分类{/if}</span><strong>{$group.name|escape:'html'}</strong><small>{$group.code_prefix|default:'未设置前缀'|escape:'html'} · {$group.image_count} 张作品 · {$group.child_count} 个子板块{if $group.direct_upload} · 可直接上传{/if}</small></div>
                <span class="gzca-status {if $group.visible eq 'true'}is-online{else}is-offline{/if}">{if $group.visible eq 'true'}显示中{else}已隐藏{/if}</span>
                <a class="gzca-button gzca-button-quiet" href="{$GZCA_URLS.categories|escape:'html'}&amp;edit={$group.id}">编辑</a>
              </article>
              {if $group.child_count gt 0}<div class="gzca-category-children">
                {foreach from=$group.children item=category}<article>
                  <div><span class="gzca-category-level">{if $category.reserved}预留画展{elseif $category.category_kind eq 'exhibition'}专项画展{else}小分类{/if}</span><strong>{$category.name|escape:'html'}</strong><small>{$category.code_prefix|default:'未设置前缀'|escape:'html'} · {$category.image_count} 张作品{if $category.direct_upload} · 可直接上传{/if}</small></div>
                  <span class="gzca-status {if $category.visible eq 'true'}is-online{else}is-offline{/if}">{if $category.visible eq 'true'}显示中{else}已隐藏{/if}</span>
                  <a class="gzca-button gzca-button-quiet" href="{$GZCA_URLS.categories|escape:'html'}&amp;edit={$category.id}">编辑</a>
                </article>{/foreach}
              </div>{/if}
            </section>
          {foreachelse}<div class="gzca-empty"><strong>还没有前台板块</strong><span>点击“建立前端对应分类”，或从左侧创建第一个大板块。</span></div>{/foreach}
          </div>
        </section>
      </div>
    {/if}

    {if $GZCA_TAB eq 'competition'}
      <div class="gzca-category-layout">
        <section class="gzca-panel gzca-category-form-panel">
          <div class="gzca-panel-head"><div><p>{if $GZCA_EDIT_COMPETITION_MEDIUM}编辑类型{else}新增类型{/if}</p><h3>{if $GZCA_EDIT_COMPETITION_MEDIUM}{$GZCA_EDIT_COMPETITION_MEDIUM.name|escape:'html'}{else}设置比赛分类{/if}</h3></div>{if $GZCA_EDIT_COMPETITION_MEDIUM}<a class="gzca-text-link" href="{$GZCA_URLS.competition|escape:'html'}">取消编辑</a>{/if}</div>
          <form class="gzca-stack-form" action="{$GZCA_URLS.competition|escape:'html'}" method="post">
            <input type="hidden" name="pwg_token" value="{$GZCA_TOKEN|escape:'html'}"><input type="hidden" name="gzca_action" value="save_competition_medium">{if $GZCA_EDIT_COMPETITION_MEDIUM}<input type="hidden" name="medium_id" value="{$GZCA_EDIT_COMPETITION_MEDIUM.id}">{/if}
            <label class="gzca-field"><span>比赛类型名称</span><input type="text" name="name" value="{if $GZCA_EDIT_COMPETITION_MEDIUM}{$GZCA_EDIT_COMPETITION_MEDIUM.name|escape:'html'}{/if}" placeholder="例如 水墨" required></label>
            <label class="gzca-field"><span>英文标识</span><input type="text" name="slug" value="{if $GZCA_EDIT_COMPETITION_MEDIUM}{$GZCA_EDIT_COMPETITION_MEDIUM.slug|escape:'html'}{/if}" placeholder="例如 ink" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" required><small>前台筛选链接使用，只能填写小写字母、数字和短横线。</small></label>
            <label class="gzca-field"><span>比赛说明</span><textarea name="description" rows="4" placeholder="例如 水墨类比赛作品">{if $GZCA_EDIT_COMPETITION_MEDIUM}{$GZCA_EDIT_COMPETITION_MEDIUM.description|escape:'html'}{/if}</textarea></label>
            <label class="gzca-field"><span>排序数字</span><input type="number" name="sort_order" value="{if $GZCA_EDIT_COMPETITION_MEDIUM}{$GZCA_EDIT_COMPETITION_MEDIUM.sort_order}{else}0{/if}" min="0"><small>数字越小越靠前。</small></label>
            <label class="gzca-field"><span>状态</span><select name="status"><option value="active" {if !$GZCA_EDIT_COMPETITION_MEDIUM or $GZCA_EDIT_COMPETITION_MEDIUM.status eq 'active'}selected{/if}>启用</option><option value="inactive" {if $GZCA_EDIT_COMPETITION_MEDIUM and $GZCA_EDIT_COMPETITION_MEDIUM.status eq 'inactive'}selected{/if}>停用</option></select></label>
            <button class="gzca-button gzca-button-primary gzca-button-block" type="submit">保存比赛类型</button>
          </form>
        </section>

        <section class="gzca-panel">
          <div class="gzca-panel-head"><div><p>前台筛选</p><h3>比赛类型</h3></div><span class="gzca-help">共 {count($GZCA_COMPETITION_MEDIA)} 类</span></div>
          <div class="gzca-category-list">
          {foreach from=$GZCA_COMPETITION_MEDIA item=medium}
            <article>
              <div><span class="gzca-category-level">比赛</span><strong>{$medium.name|escape:'html'}</strong><small>{$medium.slug|escape:'html'} · {$medium.work_count} 张已上架作品{if $medium.description} · {$medium.description|escape:'html'}{/if}</small></div>
              <span class="gzca-status {if $medium.status eq 'active'}is-online{else}is-offline{/if}">{if $medium.status eq 'active'}使用中{else}已停用{/if}</span>
              <a class="gzca-button gzca-button-quiet" href="{$GZCA_URLS.competition|escape:'html'}&amp;edit={$medium.id}">编辑</a>
            </article>
          {foreachelse}<div class="gzca-empty"><strong>还没有比赛类型</strong><span>从左侧新增第一个比赛类型。</span></div>{/foreach}
          </div>
        </section>
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
            <label class="gzca-field"><span>客服微信号</span><input type="text" name="wechat" value="{$GZCA_CONFIG.wechat|escape:'html'}" required></label>
            <label class="gzca-field"><span>联系电话</span><input type="text" name="phone" value="{$GZCA_CONFIG.phone|escape:'html'}" placeholder="可不填写"></label>
            <label class="gzca-field"><span>联系说明</span><textarea name="contact_note" rows="5">{$GZCA_CONFIG.contact_note|escape:'html'}</textarea></label>
            <label class="gzca-check"><input type="checkbox" name="frontend_sync" value="1" {if $GZCA_CONFIG.frontend_sync}checked{/if}><span><strong>同步到网站客服弹窗</strong><small>会替换前台显示的微信号、说明和二维码。</small></span></label>
            {if $GZCA_IS_WEBMASTER}<div class="gzca-info-line"><strong>后台入口</strong><small>所有后台访问默认展示国展定制后台，原生 Piwigo 后台已隐藏。</small></div>{/if}
          </div>
        </section>
        <aside class="gzca-panel gzca-qr-panel">
          <div class="gzca-panel-head"><div><p>网站标识</p><h3>公司 Logo</h3></div></div>
          <div class="gzca-qr-preview gzca-logo-preview">{if $GZCA_LOGO_URL}<img src="{$GZCA_LOGO_URL|escape:'html'}" alt="当前公司 Logo">{else}<span>图</span>{/if}</div>
          <label class="gzca-field"><span>更换 Logo</span><input type="file" name="brand_logo" accept="image/jpeg,image/png,image/webp"><small>建议透明 PNG 或 WebP，最大 4MB。</small></label>
          <div class="gzca-panel-head"><div><p>扫码联系</p><h3>客服二维码</h3></div></div>
          <div class="gzca-qr-preview">{if $GZCA_QR_URL}<img src="{$GZCA_QR_URL|escape:'html'}" alt="当前客服二维码">{else}<span>二维码占位</span>{/if}</div>
          <label class="gzca-field"><span>更换二维码</span><input type="file" name="contact_qr" accept="image/jpeg,image/png,image/webp"><small>支持 JPG、PNG、WebP，最大 2MB。</small></label>
          <button class="gzca-button gzca-button-primary gzca-button-block" type="submit">保存客服信息</button>
        </aside>
      </form>
    {/if}
  </main>
</div>
