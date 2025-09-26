# パフォーマンス関連の変更

- `Entity::checkBlockIntersections` とそれに関連する関数を削除
- `Item::onTickWorn` とそれに関連する関数を削除 (TurtleHelmet など)
- `Player::canInteract` は角度をチェックしなくなりました
- `NetworkBroadcastUtils::broadcastPackets` は3つの引数(callEvent)
  を受け入れるようになり、ハードコードされていた箇所を簡略化し、 `PacketBroadcaster` サポートを破棄しました
- `NetworkBroadcastUtils::broadcastPacketsToSession` を追加
- `broadcastEntityEvent` の改良されたバージョンの `NetworkBroadcastUtils::broadcastEntityEventToSession`
  を追加し、 `EntityEventBroadcaster` サポートを破棄しました
- `Compressor` サポートを破棄しました (`ZlibCompressor` のみサポート)
- `Living` のアーマーインベントリはコンパウンドデータをクライアントと同期しなくなりました (耐久値や、染色状態など)
- `Entity::getDirectionVector` と `Entity::getDirectionPlane` はキャッシュを使用するように
- `MathHelper` を追加
- 一部の三角関数 (sin, cos) はテーブルを使用するように
- プレイヤーのオングラウンド判定を軽量化 (`Player::handleMovementFromNetwork`)
- `World::getBlockCollisionBoxesForMovement` を追加
- エンティティはモーションをクライアントと同期しないように
- `Vector3::divide` と `Vector2::divide` を最適化
- `Entity::move` を最適化

# メジャー変更

- `EventPriority::SERVER` を追加
- `EntityDamageEvent::onPostAttack` を追加
- `World::addEntity` に `EntityFactory` に登録されていないエンティティを指定した場合に例外を発生させるロジックを削除
