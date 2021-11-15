import {Injectable} from '@angular/core';

export const WAVE_WIDTH = 1000;
export const WAVE_HEIGHT = 45;

@Injectable({
    providedIn: 'root'
})
export class WaveformGenerator {
    private audioContext: AudioContext;
    private canvas: HTMLCanvasElement;
    private context: CanvasRenderingContext2D;
    private barWidth = 3;
    private barGap = 0.5;
    private waveData: number[][] = [];

    generate(file: File) {
        if ( ! this.audioContext) {
            const AudioContext = window.AudioContext || window['webkitAudioContext'];
            this.audioContext = new AudioContext();
        }
        return new Promise(resolve => {
            // preparing canvas
            if ( ! this.context) {
                this.canvas = document.createElement('canvas');
                this.context = this.canvas.getContext('2d');
                this.canvas.width = WAVE_WIDTH;
                this.canvas.height = WAVE_HEIGHT;
            }

            // read file buffer
            const reader = new FileReader();
            reader.onload = e => {
                this.audioContext.decodeAudioData(e.target.result as ArrayBuffer, buffer => {
                    this.extractBuffer(buffer, resolve);
                });
            };
            reader.readAsArrayBuffer(file);
        });
    }

    private extractBuffer(buffer: AudioBuffer, resolve: Function) {
        const channelData = buffer.getChannelData(0);
        const sections = WAVE_WIDTH;
        const len = Math.floor(channelData.length / sections);
        const maxHeight = WAVE_HEIGHT;
        const vals = [];
        for (let i = 0; i < sections; i += this.barWidth) {
            vals.push(this.bufferMeasure(i * len, len, channelData) * 10000);
        }

        for (let j = 0; j < sections; j += this.barWidth) {
            const scale = maxHeight / (Math.max(...vals));
            let val = this.bufferMeasure(j * len, len, channelData) * 10000;
            val *= scale;
            val += 1;
            this.drawBar(j, val);
        }

        // clear canvas for redrawing
        this.context.clearRect(0, 0, WAVE_WIDTH, WAVE_HEIGHT);
        resolve(this.waveData);
        this.waveData = [];
    }

    private bufferMeasure(position: number, length: number, data: Float32Array) {
        let sum = 0.0;
        for (let i = position; i <= (position + length) - 1; i++) {
            sum += Math.pow(data[i], 2);
        }
        return Math.sqrt(sum / data.length);
    }

    private drawBar(i: number, h: number) {
        let w = this.barWidth;
        if (this.barGap !== 0) {
            w *= Math.abs(1 - this.barGap);
        }
        const x = i + (w / 2),
            y = WAVE_HEIGHT - h;

        this.waveData.push([x, y, w, h]);
    }
}
