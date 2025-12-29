<?php
/**
 * Avatar Helper
 * Generates random avatars for users without profile pictures
 */

class AvatarHelper {
    
    /**
     * Avatar styles available from DiceBear
     * See: https://www.dicebear.com/styles/
     */
    private const STYLES = [
        'adventurer',
        'adventurer-neutral', 
        'avataaars',
        'big-ears',
        'big-smile',
        'bottts',
        'croodles',
        'fun-emoji',
        'lorelei',
        'micah',
        'miniavs',
        'notionists',
        'open-peeps',
        'personas',
        'pixel-art',
        'thumbs',
    ];
    
    /**
     * Get avatar URL for a user
     * Returns their avatar if they have one, otherwise generates a random one
     */
    public static function get(?string $avatar, string $seed, int $size = 40): string {
        // If user has an avatar, use it
        if (!empty($avatar)) {
            return $avatar;
        }
        
        // Generate random avatar using DiceBear API
        return self::generate($seed, $size);
    }
    
    /**
     * Generate a random avatar URL using DiceBear
     */
    public static function generate(string $seed, int $size = 40, ?string $style = null): string {
        // Use consistent style based on seed for the same user
        if (!$style) {
            $styleIndex = abs(crc32($seed)) % count(self::STYLES);
            $style = self::STYLES[$styleIndex];
        }
        
        // DiceBear API v7
        return "https://api.dicebear.com/7.x/{$style}/svg?seed=" . urlencode($seed) . "&size={$size}";
    }
    
    /**
     * Generate avatar using UI Avatars (text-based)
     */
    public static function generateInitials(string $name, int $size = 40): string {
        $colors = ['7c3aed', '3b82f6', '10b981', 'f59e0b', 'ef4444', '8b5cf6', 'ec4899'];
        $colorIndex = abs(crc32($name)) % count($colors);
        $bg = $colors[$colorIndex];
        
        return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&size={$size}&background={$bg}&color=fff";
    }
    
    /**
     * Get avatar HTML <img> tag
     */
    public static function img(?string $avatar, string $seed, string $class = 'avatar', int $size = 40): string {
        $url = self::get($avatar, $seed, $size);
        return '<img src="' . htmlspecialchars($url) . '" class="' . $class . '" alt="Avatar">';
    }
}

/**
 * Shortcut function
 */
function avatar(?string $avatar, string $seed, int $size = 40): string {
    return AvatarHelper::get($avatar, $seed, $size);
}
