<div class="[Module]App">
  {foreach $stubs as $stub}
      {$stub nofilter}
  {/foreach}

  {include file="./export.tpl"}
</div>