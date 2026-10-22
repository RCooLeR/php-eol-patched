<?php
interface OrdinaryCacheInterface {}
class OrdinaryCacheBase {}
class OrdinaryCacheChild extends OrdinaryCacheBase implements OrdinaryCacheInterface {
    public OrdinaryCacheBase|OrdinaryCacheInterface|null $item = null;
    public function accept(OrdinaryCacheBase|OrdinaryCacheInterface $item): OrdinaryCacheBase|OrdinaryCacheInterface { return $item; }
}
enum OrdinaryCachedEnum: string { case Value = 'value'; }
