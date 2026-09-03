<?php
/**
 * step6 は早期 exit により実質無効だったため、締めダッシュボードでは非表示。
 * 繰越は step8_renew（ClosingHelper::runStep8 / carryForward2）を使用すること。
 * このファイルは互換のため残置（直接実行時は案内のみ）。
 */
echo "DEPRECATED: step6 is disabled. Use closing.php Phase A (step5 / step7 / step8) instead.\n";
exit(0);
