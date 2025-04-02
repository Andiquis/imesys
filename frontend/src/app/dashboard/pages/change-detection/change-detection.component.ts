import { Component, ElementRef, ViewChild, AfterViewChecked, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { Message } from './models/message.model';
import { ChatService } from '../../../services/chat.service';

@Component({
  selector: 'app-chat',
  standalone: true,
  imports: [CommonModule, HttpClientModule],
  templateUrl: './change-detection.component.html',
  styleUrls: ['./change-detection.component.css']
})
export default class ChatComponent implements AfterViewChecked {
  @ViewChild('messageInput') messageInput!: ElementRef<HTMLTextAreaElement>;
  @ViewChild('chatMessages') chatMessages!: ElementRef<HTMLDivElement>;

  messages: Message[] = [
    {
      sender: 'IMESYS',
      text: '¡Hola! Soy IMESYS, tu asistente virtual. ¿En qué puedo ayudarte hoy?',
      time: this.getCurrentTime(),
      isSent: false
    }
  ];

  constructor(
    private chatService: ChatService,
    private cdRef: ChangeDetectorRef
  ) {}

  ngAfterViewChecked(): void {
    this.scrollToBottom();
  }

  async sendMessage(): Promise<void> {
    const message = this.messageInput.nativeElement.value.trim();
    if (!message) return;

    // Add user message
    this.addMessage('Yo', message, true);
    this.clearInput();

    // Show typing indicator
    this.showTypingIndicator();

    try {
      // Get response from backend
      const response = await this.chatService.sendMessage(message);
      this.addMessage('IMESYS', response, false);
    } catch (error) {
      this.addMessage('Sistema', 'Error al conectar con el servidor', false);
      console.error('Error:', error);
    } finally {
      this.removeTypingIndicator();
    }
  }

  private addMessage(sender: string, text: string, isSent: boolean): void {
    const newMessage: Message = {
      sender,
      text,
      time: this.getCurrentTime(),
      isSent
    };
    this.messages = [...this.messages, newMessage];
    this.cdRef.detectChanges();
  }

  private clearInput(): void {
    this.messageInput.nativeElement.value = '';
    this.adjustTextareaHeight();
  }

  private showTypingIndicator(): void {
    const typingMessage: Message = {
      sender: 'IMESYS',
      text: 'typing',
      time: '',
      isSent: false
    };
    this.messages = [...this.messages, typingMessage];
    this.cdRef.detectChanges();
  }

  private removeTypingIndicator(): void {
    if (this.messages[this.messages.length - 1]?.text === 'typing') {
      this.messages = this.messages.slice(0, -1);
      this.cdRef.detectChanges();
    }
  }

  onKeyPress(event: KeyboardEvent): void {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      this.sendMessage();
    }
  }

  private adjustTextareaHeight(): void {
    const textarea = this.messageInput.nativeElement;
    textarea.style.height = 'auto';
    textarea.style.height = `${textarea.scrollHeight}px`;
  }

  private scrollToBottom(): void {
    setTimeout(() => {
      try {
        this.chatMessages.nativeElement.scrollTop = this.chatMessages.nativeElement.scrollHeight;
      } catch (err) {
        console.error('Error al hacer scroll:', err);
      }
    }, 0);
  }

  private getCurrentTime(): string {
    const now = new Date();
    return now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
  }
}