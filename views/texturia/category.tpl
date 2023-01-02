<h1>Товары категории {$rsCategory['name']}</h1>
{if $rsProducts || $rsChildCats !== null}
	<div class="joomcat">
		<div class="joomcat96_row">
			{foreach $rsProducts as $item name=products}
				<div style="width: 216px !important; margin-right: 10px;" class="joomcat96_imgct">
					<div class="joomcat96_img img">
						<a href="/product/{$item['id']}/">
							<img src="{$ImageWebPath}/images/products/{$item['image']}" width="200" height="150">
						</a>
					</div>
					<div style="padding-bottom: 10px; padding-top: 0px;" class="joomcat96_txt">
						<ul>
							<li><a href="/product/{$item['id']}/">{$item['name']}</a></li>
						</ul>
					</div>
				</div>
				{if $smarty.foreach.products.iteration mod 3 == 0}
					<div class="joomcat96_clr"></div>
				</div>
				<div class="joomcat96_row">
				{/if}
			{/foreach}
		</div>
		{if $rsProducts}
			<div class="pagination">
				{if $paginator['currentPage'] != 1}
					<span class="p_prev"><a href="{$paginator['link']}{$paginator['currentPage']-1}">&nbsp;</a></span>
				{/if}
				<strong><span>{$paginator['currentPage']}</span></strong>
				{if $paginator['currentPage'] < $paginator['pageCnt']}
					<span class="p_next"><a href="{$paginator['link']}{$paginator['currentPage']+1}">&nbsp;</a></span>
				{/if}
			</div>
		{/if}
	</div>
	{foreach $rsChildCats as $item}
		<h2><a href="/category/{$item['id']}/">{$item['name']}</a></h2>
	{/foreach}
{else}
	<h2>Ничего не найдено</h2>
{/if}