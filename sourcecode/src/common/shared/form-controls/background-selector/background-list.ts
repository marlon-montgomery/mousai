export interface BackgroundConfig {
    configId: string;
    backgroundColor?: string;
    backgroundAttachment?: string;
    backgroundSize?: string;
    backgroundRepeat?: string;
    backgroundPosition?: string;
    backgroundImage?: string;
    color?: string;
    configLabel?: string;
}

export const uploadedImgBg = {
    configId: 'uploadedImg',
    configLabel: 'Image',
    backgroundSize: 'cover',
    repeat: 'no-repeat',
    position: 'center center',
};

export const flatColorBg = {
    configId: 'flat',
    configLabel: 'Color',
    backgroundColor: 'rgb(209, 246, 255)',
    color: null,
};

export const gradientBg = {
    configId: 'gradient',
    configLabel: 'Gradient',
    backgroundImage: 'linear-gradient(43deg, #4158D0 0%, #C850C0 46%, #FFCC70 100%)'
};

export const BACKGROUND_LIST: BackgroundConfig[] = [
    uploadedImgBg,
    flatColorBg,
    gradientBg,
    {
        configId: 'img1',
        backgroundColor: '#ffdd55',
        backgroundImage: 'svg-bgs/Angled-Focus.svg',
        backgroundAttachment: 'fixed',
        backgroundSize: 'cover',
        color: '#fff',
    },
    {
        configId: 'img2',
        backgroundColor: '#220044',
        backgroundImage: 'svg-bgs/Circular-Focus.svg',
        backgroundAttachment: 'fixed',
        backgroundSize: 'cover',
        color: '#fff',
    },
    {
        configId: 'img3',
        backgroundColor: '#000000',
        backgroundImage: 'svg-bgs/Farseeing-Eyeball.svg',
        backgroundAttachment: 'fixed',
        backgroundSize: 'cover',
        color: '#fff',
    },
    {
        configId: 'img4',
        backgroundColor: '#ff0000',
        backgroundImage: 'svg-bgs/Canyon-Funnel.svg',
        backgroundAttachment: 'fixed',
        backgroundSize: 'cover',
        color: '#fff',
    },
    {
        configId: 'img5',
        backgroundColor: '#11ddaa',
        backgroundImage: 'svg-bgs/Looney-Loops.svg',
        backgroundAttachment: 'fixed',
        backgroundSize: 'cover',
        color: '#000',
    },
    {
        configId: 'img6',
        backgroundColor: '#070014',
        backgroundImage: 'svg-bgs/Hurricane-Aperture.svg',
        backgroundAttachment: 'fixed',
        backgroundSize: 'cover',
        color: '#fff',
    },
    {
        configId: 'img7',
        backgroundColor: '#ccffff',
        backgroundImage: 'svg-bgs/Icy-Explosion.svg',
        backgroundAttachment: 'fixed',
        backgroundSize: 'cover',
        backgroundRepeat: 'no-repeat',
        color: '#000',
    },
    {
        configId: 'img8',
        backgroundColor: '#442233',
        backgroundImage: 'svg-bgs/Nuclear-Focalpoint.svg',
        backgroundAttachment: 'fixed',
        backgroundSize: 'cover',
        color: '#fff',
    },
    {
        configId: 'img9',
        backgroundColor: '#ee5522;',
        backgroundImage: 'svg-bgs/Protruding-Squares.svg',
        color: '#fff',
    },
    {
        configId: 'img10',
        backgroundColor: '#fff',
        backgroundImage: 'svg-bgs/Alternating-Triangles.svg',
        color: '#000',
    },
    {
        configId: 'img11',
        backgroundColor: '#002200',
        backgroundImage: 'svg-bgs/Monstera-Patch.svg',
        color: '#fff',
    },
    {
        configId: 'img11',
        backgroundColor: '#aa3333',
        backgroundImage: 'svg-bgs/Confetti-Doodles.svg',
        color: '#fff',
        backgroundAttachment: 'fixed',
    },
    {
        configId: 'img12',
        backgroundColor: '#ffdd99',
        backgroundImage: 'svg-bgs/Threads-Ahead.svg',
        backgroundAttachment: 'fixed',
        backgroundSize: 'cover',
        color: '#000',
    },
    {
        configId: 'img13',
        backgroundColor: '#00bbff',
        backgroundImage: 'svg-bgs/Launch-Day.svg',
        backgroundAttachment: 'fixed',
        backgroundSize: 'cover',
        color: '#fff',
    },
    {
        configId: 'img14',
        backgroundImage: 'svg-bgs/Sprinkle.svg',
    },
    {
        configId: 'img15',
        backgroundImage: 'svg-bgs/Circuit-Board.svg',
    },
    {
        configId: 'img15',
        backgroundImage: 'svg-bgs/Snow.svg',
    },
];
