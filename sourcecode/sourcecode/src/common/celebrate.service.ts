import {DOCUMENT} from '@angular/common';
import ConfettiGenerator from 'confetti-js';
import {Inject, Injectable} from '@angular/core';

export enum ConfettiSvg {
    DIAMOND = 'diamond-colored',
}

const svgToProps = {
    [ConfettiSvg.DIAMOND]: {size: 10, weight: 1},
};

@Injectable({
    providedIn: 'root'
})
export class CelebrateService {
    confetti: any;
    canvasCount = 0;

    constructor(@Inject(DOCUMENT) protected document: HTMLDocument) {
        for (let i = 0; i < 5; i++) {
            const element = this.document.createElement('canvas');

            element.id = `my-canvas-${i}`;

            element.style.top = '0';
            element.style.left = '0';
            element.style.width = '100%';
            element.style.zIndex = '2000';
            element.style.height = '100%';
            element.style.position = 'fixed';
            element.style.pointerEvents = 'none';

            this.document.body.appendChild(element);
        }
    }

    render(svgList: ConfettiSvg[] = []) {
        const canvasID = `my-canvas-${this.canvasCount}`;

        this.canvasCount++;
        this.canvasCount = this.canvasCount % 5;

        const confettiSettings: { [key: string]: any } = {
            target: canvasID,
            max: 500,
            respawn: false,
            size: 2,
            start_from_edge: true,
            rotate: true,
            clock: 100,
        };

        if (svgList.length > 0) {
            const prefix = window.location.hostname === 'localhost' ? '' : 'client/';
            const callback = (svg) => ({
                ...{
                    type: 'svg',
                    src: `${prefix}/assets/icons/individual/${svg}.svg`
                }, ...svgToProps[svg]
            });

            confettiSettings.props = svgList.map(callback);

            confettiSettings.clock = svgList.indexOf(ConfettiSvg.DIAMOND) >= 0 ? 1000 : 75;
            confettiSettings.max = 200;
        }

        this.confetti = new ConfettiGenerator(confettiSettings);
        this.confetti.render();
    }

    rain() {
        return {
            diamond: () => this.render([ConfettiSvg.DIAMOND])
        };
    }
}
