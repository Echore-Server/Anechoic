# Changes

- Removed `Entity::checkBlockIntersections` and related properties
- Removed `Item::onTickWorn` and related functions (such as Turtle Helmet)
- `Player::canInteract` no longer validates angles
- `NetworkBroadcastUtils::broadcastPackets` is now accepts three arguments (callEvent), simplified hardcoded sections,
  dropped `PacketBroadcaster` support
- Added `NetworkBroadcastUtils::broadcastPacketsToSession`
- Added `NetworkBroadcastUtils::broadcastEntityEventToSession`, improved version of `broadcastEntityEvent`,
  dropped `EntityEventBroadcaster` support
- Dropped `Compressor` support (only `ZlibCompressor`)
- Living's armor inventory compound data is no longer synced (such as durability, dye colors)
- `Entity::getDirectionVector`, `Entity::getDirectionPlane` are now using cache
- Added `MathHelper`
- Mathematics functions (sin, cos) are now using table
- Improved performance of player's on ground check (`Player::handleMovementFromNetwork`)
- Added `World::getBlockCollisionBoxesForMovement`
- Entities no longer broadcast motion
- Optimized `Vector3::divide`, `Vector2::divide`
- Optimized `Entity::move`
- Added `EventPriority::SERVER`
