<?php
declare(strict_types=1);

namespace RealYXNG\DeathHead;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\Item;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener
{
    private Config $config;

    protected function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, [
            "head" => 0,
            "type" => "steve",
            "number" => true,
            "enabled" => true
        ]);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
{
    if ($command->getName() === "dh") {
        $subcommand = $args[0] ?? null;

        switch ($subcommand) {
            case "help":
                $sender->sendMessage("```
                    /dh help - Muestra la ayuda del plugin
                    /dh type <steve/skull> - Establece el tipo de cabeza
                    /dh number <true/false> - Establece si se muestra el número de la cabeza en la descripción
                    /dh enabled <true/false> - Activa/desactiva la caída de cabezas
                ```");
                break;
            case "type":
                if (count($args) < 2) {
                    $sender->sendMessage("Uso: /dh type <steve/skull>");
                    return true;
                }

                $type = $args[1];
                if ($type !== "steve" && $type !== "skull") {
                    $sender->sendMessage("El tipo de cabeza debe ser 'steve' o 'skull'");
                    return true;
                }

                $this->config->set("type", $type);
                $this->config->save();
                $sender->sendMessage("Se ha establecido el tipo de cabeza a '" . $type . "'");
                break;
            case "number":
                if (count($args) < 2) {
                    $sender->sendMessage("Uso: /dh number <true/false>");
                    return true;
                }

                $number = $args[1];
                if ($number !== "true" && $number !== "false") {
                    $sender->sendMessage("El número de la cabeza debe ser 'true' o 'false'");
                    return true;
                }

                $this->config->set("number", $number);
                $this->config->save();
                $sender->sendMessage("Se ha establecido si se muestra el número de la cabeza a '" . $number . "'");
                break;
            case "enabled":
                if (count($args) < 2) {
                    $sender->sendMessage("Uso: /dh enabled <true/false>");
                    return true;
                }

                $enabled = $args[1];
                if ($enabled !== "true" && $enabled !== "false") {
                    $sender->sendMessage("La activación de la caída de cabezas debe ser 'true' o 'false'");
                    return true;
                }

                $this->config->set("enabled", $enabled);
                $this->config->save();
                $sender->sendMessage("Se ha activado/desactivado la caída de cabezas a '" . $enabled . "'");
                break;
            default:
                $sender->sendMessage("Comando desconocido. Usa /dh help para obtener ayuda.");
                break;
        }

        return true;
    }

    return false;
}

        return true;
    }

    public function onDeath(PlayerDeathEvent $event)
    {
        if (!$this->config->get("enabled", true)) {
            return;
        }

        $player = $event->getPlayer();
        $headNo = $this->config->get("head") + 1;
        $this->config->set("head", $headNo);
        $this->config->save();

        $skullType = $this->config->get("type", "steve");
        $skullItem = match ($skullType) {
            "steve" => ItemFactory::get(Item::PLAYER_HEAD),
            "skull" => ItemFactory::get(Item::SKELETON_SKULL),
            default => ItemFactory::get(Item::PLAYER_HEAD),
        };

        $loreNumber = $this->config->get("number", true);
        $headLore = $loreNumber ? [TextFormat::YELLOW . "Head #" . $headNo, TextFormat::LIGHT_PURPLE . "R.I.P " . $player->getName()] : [TextFormat::LIGHT_PURPLE . "R.I.P " . $player->getName()];

        $skullItem->setCustomName("§l§o§e" . $player->getName() . "'s Head");
        $skullItem->setLore($headLore);
        $event->getDrops()->add($skullItem);
    }
}
