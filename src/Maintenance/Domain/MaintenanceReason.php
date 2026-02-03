<?php

declare(strict_types=1);

namespace Maintenance\Domain;

enum MaintenanceReason: string
{
    // TRANSMISSION
    case DERAILLEUR_ADJUSTMENT = 'derailleur_adjustment';
    case DERAILLEUR_REPLACEMENT = 'derailleur_replacement';
    case CHAIN_REPLACEMENT = 'chain_replacement';
    case CHAIN_CLEANING_LUBRICATION = 'chain_cleaning_lubrication';
    case CASSETTE_REPLACEMENT = 'cassette_replacement';
    case CHAINRING_REPLACEMENT = 'chainring_replacement';
    case CRANKSET_REPLACEMENT = 'crankset_replacement';
    case BOTTOM_BRACKET_SERVICE = 'bottom_bracket_service';
    case CABLE_HOUSING_REPLACEMENT = 'cable_housing_replacement';

    // FREINS
    case BRAKE_BLEEDING = 'brake_bleeding';
    case BRAKE_PAD_REPLACEMENT = 'brake_pad_replacement';
    case BRAKE_DISC_REPLACEMENT = 'brake_disc_replacement';
    case BRAKE_CALIPER_SERVICE = 'brake_caliper_service';
    case BRAKE_LEVER_REPLACEMENT = 'brake_lever_replacement';
    case BRAKE_CABLE_REPLACEMENT = 'brake_cable_replacement';
    case BRAKE_ADJUSTMENT = 'brake_adjustment';

    // SUSPENSIONS
    case FORK_SERVICE = 'fork_service';
    case FORK_OIL_CHANGE = 'fork_oil_change';
    case FORK_SEAL_REPLACEMENT = 'fork_seal_replacement';
    case REAR_SHOCK_SERVICE = 'rear_shock_service';
    case SUSPENSION_TUNING = 'suspension_tuning';
    case SUSPENSION_LOCKOUT_REPAIR = 'suspension_lockout_repair';

    // ROUES
    case WHEEL_TRUING = 'wheel_truing';
    case SPOKE_REPLACEMENT = 'spoke_replacement';
    case RIM_REPLACEMENT = 'rim_replacement';
    case HUB_SERVICE = 'hub_service';
    case HUB_BEARING_REPLACEMENT = 'hub_bearing_replacement';
    case TIRE_REPLACEMENT = 'tire_replacement';
    case INNER_TUBE_REPLACEMENT = 'inner_tube_replacement';
    case TUBELESS_SETUP = 'tubeless_setup';
    case WHEEL_BUILDING = 'wheel_building';

    // DIRECTION
    case HEADSET_SERVICE = 'headset_service';
    case HEADSET_BEARING_REPLACEMENT = 'headset_bearing_replacement';
    case STEM_REPLACEMENT = 'stem_replacement';
    case HANDLEBAR_REPLACEMENT = 'handlebar_replacement';
    case GRIPS_BAR_TAPE_REPLACEMENT = 'grips_bar_tape_replacement';

    // CADRE
    case FRAME_INSPECTION = 'frame_inspection';
    case FRAME_CRACK_REPAIR = 'frame_crack_repair';
    case FRAME_ALIGNMENT = 'frame_alignment';
    case PAINT_TOUCH_UP = 'paint_touch_up';
    case DROPOUT_REPAIR = 'dropout_repair';

    // ÉLECTRIQUE (VAE)
    case BATTERY_CHECK = 'battery_check';
    case BATTERY_REPLACEMENT = 'battery_replacement';
    case MOTOR_SERVICE = 'motor_service';
    case MOTOR_REPLACEMENT = 'motor_replacement';
    case DISPLAY_REPLACEMENT = 'display_replacement';
    case ELECTRICAL_WIRING = 'electrical_wiring';
    case CONTROLLER_REPLACEMENT = 'controller_replacement';
    case SENSOR_REPLACEMENT = 'sensor_replacement';

    // RÉVISION COMPLÈTE
    case FULL_SERVICE_BASIC = 'full_service_basic';
    case FULL_SERVICE_ADVANCED = 'full_service_advanced';
    case PRE_SEASON_SERVICE = 'pre_season_service';
    case POST_SEASON_SERVICE = 'post_season_service';

    // AUTRES
    case SADDLE_REPLACEMENT = 'saddle_replacement';
    case SEATPOST_REPLACEMENT = 'seatpost_replacement';
    case PEDAL_REPLACEMENT = 'pedal_replacement';
    case KICKSTAND_REPAIR = 'kickstand_repair';
    case FENDER_INSTALLATION = 'fender_installation';
    case RACK_INSTALLATION = 'rack_installation';
    case LIGHTING_INSTALLATION = 'lighting_installation';
    case ACCESSORIES_INSTALLATION = 'accessories_installation';
    case GENERAL_CLEANING = 'general_cleaning';
    case SAFETY_INSPECTION = 'safety_inspection';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            // TRANSMISSION
            self::DERAILLEUR_ADJUSTMENT => 'Réglage dérailleur',
            self::DERAILLEUR_REPLACEMENT => 'Remplacement dérailleur',
            self::CHAIN_REPLACEMENT => 'Remplacement chaîne',
            self::CHAIN_CLEANING_LUBRICATION => 'Nettoyage et lubrification chaîne',
            self::CASSETTE_REPLACEMENT => 'Remplacement cassette',
            self::CHAINRING_REPLACEMENT => 'Remplacement plateau(x)',
            self::CRANKSET_REPLACEMENT => 'Remplacement pédalier',
            self::BOTTOM_BRACKET_SERVICE => 'Entretien boîtier de pédalier',
            self::CABLE_HOUSING_REPLACEMENT => 'Remplacement câble/gaine',

            // FREINS
            self::BRAKE_BLEEDING => 'Purge freins hydrauliques',
            self::BRAKE_PAD_REPLACEMENT => 'Remplacement plaquettes de frein',
            self::BRAKE_DISC_REPLACEMENT => 'Remplacement disque de frein',
            self::BRAKE_CALIPER_SERVICE => 'Entretien étrier de frein',
            self::BRAKE_LEVER_REPLACEMENT => 'Remplacement levier de frein',
            self::BRAKE_CABLE_REPLACEMENT => 'Remplacement câble de frein',
            self::BRAKE_ADJUSTMENT => 'Réglage freins',

            // SUSPENSIONS
            self::FORK_SERVICE => 'Entretien fourche',
            self::FORK_OIL_CHANGE => 'Vidange huile fourche',
            self::FORK_SEAL_REPLACEMENT => 'Remplacement joints spi fourche',
            self::REAR_SHOCK_SERVICE => 'Entretien amortisseur arrière',
            self::SUSPENSION_TUNING => 'Réglage suspensions',
            self::SUSPENSION_LOCKOUT_REPAIR => 'Réparation lockout suspension',

            // ROUES
            self::WHEEL_TRUING => 'Dévoilage roue',
            self::SPOKE_REPLACEMENT => 'Remplacement rayon(s)',
            self::RIM_REPLACEMENT => 'Remplacement jante',
            self::HUB_SERVICE => 'Entretien moyeu',
            self::HUB_BEARING_REPLACEMENT => 'Remplacement roulements moyeu',
            self::TIRE_REPLACEMENT => 'Remplacement pneu',
            self::INNER_TUBE_REPLACEMENT => 'Remplacement chambre à air',
            self::TUBELESS_SETUP => 'Montage tubeless',
            self::WHEEL_BUILDING => 'Montage roue complète',

            // DIRECTION
            self::HEADSET_SERVICE => 'Entretien jeu de direction',
            self::HEADSET_BEARING_REPLACEMENT => 'Remplacement roulements direction',
            self::STEM_REPLACEMENT => 'Remplacement potence',
            self::HANDLEBAR_REPLACEMENT => 'Remplacement cintre',
            self::GRIPS_BAR_TAPE_REPLACEMENT => 'Remplacement poignées/guidoline',

            // CADRE
            self::FRAME_INSPECTION => 'Inspection cadre',
            self::FRAME_CRACK_REPAIR => 'Réparation fissure cadre',
            self::FRAME_ALIGNMENT => 'Alignement cadre',
            self::PAINT_TOUCH_UP => 'Retouche peinture',
            self::DROPOUT_REPAIR => 'Réparation patte de dérailleur',

            // ÉLECTRIQUE (VAE)
            self::BATTERY_CHECK => 'Contrôle batterie',
            self::BATTERY_REPLACEMENT => 'Remplacement batterie',
            self::MOTOR_SERVICE => 'Entretien moteur',
            self::MOTOR_REPLACEMENT => 'Remplacement moteur',
            self::DISPLAY_REPLACEMENT => 'Remplacement afficheur',
            self::ELECTRICAL_WIRING => 'Câblage électrique',
            self::CONTROLLER_REPLACEMENT => 'Remplacement contrôleur',
            self::SENSOR_REPLACEMENT => 'Remplacement capteur',

            // RÉVISION COMPLÈTE
            self::FULL_SERVICE_BASIC => 'Révision complète de base',
            self::FULL_SERVICE_ADVANCED => 'Révision complète avancée',
            self::PRE_SEASON_SERVICE => 'Révision avant saison',
            self::POST_SEASON_SERVICE => 'Révision après saison',

            // AUTRES
            self::SADDLE_REPLACEMENT => 'Remplacement selle',
            self::SEATPOST_REPLACEMENT => 'Remplacement tige de selle',
            self::PEDAL_REPLACEMENT => 'Remplacement pédales',
            self::KICKSTAND_REPAIR => 'Réparation béquille',
            self::FENDER_INSTALLATION => 'Installation garde-boue',
            self::RACK_INSTALLATION => 'Installation porte-bagages',
            self::LIGHTING_INSTALLATION => 'Installation éclairage',
            self::ACCESSORIES_INSTALLATION => 'Installation accessoires',
            self::GENERAL_CLEANING => 'Nettoyage général',
            self::SAFETY_INSPECTION => 'Contrôle de sécurité',
            self::OTHER => 'Autre',
        };
    }

    public function category(): MaintenanceCategory
    {
        return match ($this) {
            // TRANSMISSION
            self::DERAILLEUR_ADJUSTMENT,
            self::DERAILLEUR_REPLACEMENT,
            self::CHAIN_REPLACEMENT,
            self::CHAIN_CLEANING_LUBRICATION,
            self::CASSETTE_REPLACEMENT,
            self::CHAINRING_REPLACEMENT,
            self::CRANKSET_REPLACEMENT,
            self::BOTTOM_BRACKET_SERVICE,
            self::CABLE_HOUSING_REPLACEMENT
                => MaintenanceCategory::TRANSMISSION,

            // FREINS
            self::BRAKE_BLEEDING,
            self::BRAKE_PAD_REPLACEMENT,
            self::BRAKE_DISC_REPLACEMENT,
            self::BRAKE_CALIPER_SERVICE,
            self::BRAKE_LEVER_REPLACEMENT,
            self::BRAKE_CABLE_REPLACEMENT,
            self::BRAKE_ADJUSTMENT
                => MaintenanceCategory::BRAKES,

            // SUSPENSIONS
            self::FORK_SERVICE,
            self::FORK_OIL_CHANGE,
            self::FORK_SEAL_REPLACEMENT,
            self::REAR_SHOCK_SERVICE,
            self::SUSPENSION_TUNING,
            self::SUSPENSION_LOCKOUT_REPAIR
                => MaintenanceCategory::SUSPENSION,

            // ROUES
            self::WHEEL_TRUING,
            self::SPOKE_REPLACEMENT,
            self::RIM_REPLACEMENT,
            self::HUB_SERVICE,
            self::HUB_BEARING_REPLACEMENT,
            self::TIRE_REPLACEMENT,
            self::INNER_TUBE_REPLACEMENT,
            self::TUBELESS_SETUP,
            self::WHEEL_BUILDING
                => MaintenanceCategory::WHEELS,

            // DIRECTION
            self::HEADSET_SERVICE,
            self::HEADSET_BEARING_REPLACEMENT,
            self::STEM_REPLACEMENT,
            self::HANDLEBAR_REPLACEMENT,
            self::GRIPS_BAR_TAPE_REPLACEMENT
                => MaintenanceCategory::STEERING,

            // CADRE
            self::FRAME_INSPECTION,
            self::FRAME_CRACK_REPAIR,
            self::FRAME_ALIGNMENT,
            self::PAINT_TOUCH_UP,
            self::DROPOUT_REPAIR
                => MaintenanceCategory::FRAME,

            // ÉLECTRIQUE (VAE)
            self::BATTERY_CHECK,
            self::BATTERY_REPLACEMENT,
            self::MOTOR_SERVICE,
            self::MOTOR_REPLACEMENT,
            self::DISPLAY_REPLACEMENT,
            self::ELECTRICAL_WIRING,
            self::CONTROLLER_REPLACEMENT,
            self::SENSOR_REPLACEMENT
                => MaintenanceCategory::ELECTRICAL,

            // RÉVISION COMPLÈTE
            self::FULL_SERVICE_BASIC,
            self::FULL_SERVICE_ADVANCED,
            self::PRE_SEASON_SERVICE,
            self::POST_SEASON_SERVICE
                => MaintenanceCategory::FULL_SERVICE,

            // AUTRES
            self::SADDLE_REPLACEMENT,
            self::SEATPOST_REPLACEMENT,
            self::PEDAL_REPLACEMENT,
            self::KICKSTAND_REPAIR,
            self::FENDER_INSTALLATION,
            self::RACK_INSTALLATION,
            self::LIGHTING_INSTALLATION,
            self::ACCESSORIES_INSTALLATION,
            self::GENERAL_CLEANING,
            self::SAFETY_INSPECTION,
            self::OTHER
                => MaintenanceCategory::OTHER,
        };
    }

    /**
     * Obtenir toutes les raisons d'une catégorie donnée
     * @return MaintenanceReason[]
     */
    public static function byCategory(MaintenanceCategory $category): array
    {
        return array_filter(
            self::cases(),
            fn(self $reason) => $reason->category() === $category
        );
    }
}
