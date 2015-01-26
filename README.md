otu(乙)
===

荒らしを牢屋に入れたり、動けなくしたり荒らしの行動を制限することができます。<br/>
またJailPluginプラグインも移植しています。(許可なし...)<br/>
<br/>
otuとrunaのアイデア:akaituki8126<br/>
JailPluginの作者様:omattyao<br/>
<br/>
###コマンド

####乙、ルナ
<table>
  <tr>
    <td>コマンド</td>
    <td>説明</td>
    <td>補足</td>
  </tr>
  <tr>
    <td>/otu &lt;プレーヤー名&gt;</td>
    <td>指定したプレーヤーを牢屋に入れて<br/>コマンド、ブロック破壊、設置をできなくなります</td>
    <td>解除はもう一度やれば解除できます</td>
  </tr>
  <tr>
    <td>/runa &lt;プレーヤー名&gt;</td>
    <td>指定したプレーヤーを動けなくします(要otu)</td>
    <td>解除はもう一度やれば解除できます</td>
  </tr>
 <tr>
    <td>/otup</td>
    <td>牢屋の場所を設定します</td>
    <td>設定は保存されます</td>
  </tr>
  <tr>
    <td>/otulist</td>
    <td>乙、ルナをされた人の一覧を見れます</td>
    <td></td>
  </tr>
</table>
####牢屋
<table>
  <tr>
    <td>コマンド</td>
    <td>説明</td>
    <td>補足</td>
  </tr>
 <tr>
    <td>/jail &lt;プレーヤー名&gt; [牢屋のタイプ]</td>
    <td>プレーヤーをその場で牢屋に入れます</td>
    <td>牢屋のタイプの変更は未実装</td>
  </tr>
  <tr>
    <td>/unjail</td>
    <td>設置された牢屋を撤去し、元通りの状態に戻します</td>
    <td></td>
  </tr>
  <tr>
    <td>/unjailall</td>
    <td>設置されたすべての牢屋を撤去します</td>
    <td></td>
  </tr>
</table>
####牢屋の作成
<table>
  <tr>
    <td>コマンド</td>
    <td>説明</td>
    <td>補足</td>
  </tr>
  <tr>
    <td>/jailcraft craft &lt;牢屋の名前&gt;</td>
    <td>牢屋を作成できます。</td>
    <td></td>
  </tr>
   <tr>
    <td>/jailcraft pos1</td>
    <td>始点を指定します。</td>
    <td>プレーヤーの位置で指定されます</td>
  </tr>
  <tr>
    <td>/jailcraft pos2</td>
    <td>終点を指定します。</td>
    <td>プレーヤーの位置で指定されます</td>
  </tr>
   <tr>
    <td>/jailcraft pos3</td>
    <td>プレーヤーの位置を指定します。</td>
    <td>プレーヤーの位置で指定されます</td>
  </tr>
</table>
###設定
 : (コロン)以降を変更してください<br/>
test: aaa -> test: abc <br/>
有効/true 無効/false<br/>
setting.yml<br/>
####乙を解除された際に設定されたコマンドを実行するか<br/>
```yaml
syakuhou: true
```
####乙解除された際に実行するコマンド<br/>
※[syakuhou]が有効になっている必要があります<br/>
※コマンドはコンソールとして実行されます。
#####変数 
%p otuを解除された人の名前 %cp otuを解除した人の名前<br/>
%x %y %z コマンドを実行したプレーヤーの座標<br/>
%cx,%cy,%cz otuを解除された人の座標<br/>
#####例
```yaml
#以下のコマンドを実行するとotuを解除された人がotuを解除した人のところに行きます。
otuoffcmd: tp %p %cp
# / を付けると複数実行できます。　
# 例
otuoffcmd: tp %p %cp/say [乙]　%cpさんが釈放されました
```
